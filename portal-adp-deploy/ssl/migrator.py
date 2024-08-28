import requests
import pprint
import sys
import json
class Migrator:
    def __init__(self, database, secure=True):
        self.secure=secure
        print database
        self.database=database

    def view(self, view, do_filter=lambda d:d):
        full="{0}/{1}".format(self.database,view)
        return [r.get(u'value', {})
                for r in requests.get(full,verify=self.secure).json().get(u'rows',[])
                if do_filter(r.get(u'value', {}))
                ]

    def update(self, update):
        cmd = "{0}/{1}".format(self.database, "_bulk_docs")
        return requests.post(cmd, json=update, verify=self.secure).json()

class ChangeSet:
    @staticmethod
    def create(rows, make_change):
        update = {u"docs": []}
        for row in rows:
            make_change(row)
            update[u"docs"].append(row)
        return update

class Microservice:
    _dbUrl = '_design/adp/_view/microserviceView'

    ID = u'_id'
    REV = u'_rev'

    CATEGORY = u'category'
    STATUS = u'status'
    TYPE = u'serviceType'
    STRATEGY = u'adp_strategy'
    APPROVAL_COMMENT = u'approval_comment'
    NAME = u'name'
    HELM_CHARTNAME = u'helm_chartname'
    HELMURL = u'helmurl'
    GITURL = u'giturl'
    REALIZATION = u'adp_realization'
    DOCUMENTATION = u'documentation'
    TEAM_MAILS = u'teamMails'
    VERSION = u'version'
    LIKES = u'likes'
    TEAM = u'team'
    OWNER = u'owner'
    APPROVAL = u'approval'
    IMAGE = u'image'
    DESC = u'description'
    ALIGNMENT = u'alignment'

class User:
    _dbUrl = '_design/adp/_view/userView'
    ID = u'_id'
    REV = u'_rev'

    TYPE = u'type'
    SIGNUM = u'signum'
    NAME = u'name'
    EMAIL = u'email'
    ROLE = u'role'
    MODIFIED = u'modified'

class Documentation:
    TYPE = u"type"
    DOCCHARTNAME = u"docChartName"
    URL = u"url"
    TITLEID = "titleId"
    CATEGORYID = "categoryId"



def make_change(row):
    # handle the migration of the git and helm data
    if Microservice.DOCUMENTATION in row:
        gitdocs = []
        helmdocs= []
        otherdocs=[]
        for document in row[Microservice.DOCUMENTATION]:
            if Documentation.TYPE in document:
                if document[Documentation.TYPE] == "2" or document[Documentation.TYPE] == 2:
                    gitdocs.append(document)
                elif document[Documentation.TYPE] == "3" or document[Documentation.TYPE] == 3:
                    helmdocs.append(document)
                else:
                    otherdocs.append(document)
        row[Microservice.DOCUMENTATION] = otherdocs
        if gitdocs:
            row[Microservice.GITURL] = gitdocs[0][Documentation.URL]
            
        if helmdocs:
            if Documentation.URL in helmdocs[0]:
                row[Microservice.HELMURL] = helmdocs[0][Documentation.URL]
            if Documentation.DOCCHARTNAME in helmdocs[0]:
                row[Microservice.HELM_CHARTNAME] = helmdocs[0][Documentation.DOCCHARTNAME]
    # Handle the document field update 
    with open("data.json") as data_file:
        f = json.load(data_file)
    
    for file_row in f["rows"]:
        file_row_data = file_row["value"]
        if file_row_data[Microservice.ID] == row[Microservice.ID]:
            if Microservice.DOCUMENTATION in file_row_data:
                row[Microservice.DOCUMENTATION] = file_row_data[Microservice.DOCUMENTATION]


def summarise(tracker, parent, fieldname):
    if fieldname in parent:
        if parent[fieldname] not in tracker:
            tracker[parent[fieldname]]=0
        tracker[parent[fieldname]]+=1

def docReport(docs):
    giturls=0
    helmurls=0
    helmcharts=0
    cats = {}
    titles = {}
    for doc in docs:
        if Microservice.GITURL in doc:
            giturls +=1
        if Microservice.HELMURL in doc:
            helmurls +=1
        if Microservice.HELM_CHARTNAME in doc:
            helmcharts +=1
        if Microservice.DOCUMENTATION in doc:
            for document in doc[Microservice.DOCUMENTATION]:
                summarise(cats, document, Documentation.CATEGORYID)
                summarise(titles, document, Documentation.TITLEID)
    print "Categories" 
    pprint.pprint(cats)
    print "Titles"
    pprint.pprint(titles)
    print "HELM: " + str(helmcharts) + " charts and " + str(helmurls) + " urls"
    print "git: " + str(giturls) + " urls"
        

if __name__ == "__main__":
    secure=False
    def_root_url="https://131.160.143.86:48083"
    if len(sys.argv)>1:
        db_root_url=sys.argv[1]
    else:
        db_root_url=def_root_url
    m = Migrator(db_root_url+"/adp", secure)
    def microservice_filter(row):
        return u"microservice" in row[u"type"]
    def admin_user_filter(row):
        return u"user" in row[u"type"] and "esupuse" in row[User.SIGNUM]
    d = m.view(Microservice._dbUrl, microservice_filter)
    docReport(d)
    c=None
    if d:
        c = ChangeSet.create(d,make_change)

    print "-------------"  
    docReport(c["docs"])
    with open("output.json", "w+") as proposed:
        json.dump(c, proposed)

    res = m.update(c)
    pprint.pprint(res)
