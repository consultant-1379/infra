import requests
import pprint
import sys
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
    
    def create(self, createRecord):
        cmd = "{0}".format(self.database)
        return requests.post(cmd, json=createRecord, verify=self.secure).json()

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

def make_change(row):
    if row[User.SIGNUM] == u'esupuse':
        row[User.ROLE] = u'admin'

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
    d = m.view(User._dbUrl, admin_user_filter)
    c=None
    if d:
        c = ChangeSet.create(d,make_change)
    if c:
        pprint.pprint(c)
        #res = m.update(c)
        #pprint.pprint(res)
    else:
        newUser={u'_id': u'esupuse',
        u'email': u'super-user@adp-test.com',
        u'marketInformationActive': True,
        u'modified': u'2018-11-02T16:14:53.780Z',
        u'name': u'Super User',
        u'role': u'admin',
        u'signum': u'esupuse',
        u'type': u'user'}
        m.create(newUser)
