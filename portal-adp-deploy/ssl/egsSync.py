import requests
import sys
import os

if __name__ == "__main__":
    parameters = { 'username': os.environ['PORTAL_FUNC_USER'], 'password': os.environ['PORTAL_FUNC_USER_PASSWORD'] }
    if len(sys.argv) > 2:
        verify = sys.argv[2].lower() == 'true'
    else:
        verify = False
    response = requests.post(sys.argv[1]+'/clientDocs/login', verify=verify, data=parameters).json()
    token = 'Bearer ' + response['data']['token']
    trigger = requests.get(sys.argv[1]+'/egssync/trigger', verify=verify, headers = { 'Authorization': token})
    print(trigger.text)