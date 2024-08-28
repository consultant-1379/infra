# Deploying AIA portal on an openstack cloud


[TOC]

## Getting Started
These scripts  automate the deployment of AIA portal onto an openstack environment using  openstack python client and cloud-init.

## Prerequisites
On the cloud:
- security group is available
- centos image is uploaded to glance
- create custom flavors
- create internal networks
- create openstack router
- users credintial file 

Openstack Project Mininuim Quota:
- instance: > 1   ( per portal VM to be deployed)
- RAM > 8GB  ( per portal VM to be deployed)
- Storage > 30GB  ( per portal VM to be deployed)
- CPU > 4  ( per portal VM to be deployed)
- Floating IP > 1  ( per portal VM to be deployed)
- Security group
- Network > 1
- Router > 1

## Deploy
The is script has been designed to work towards AIA openstack cloud project portal.
If you are using on a different cloud/project you should edit the variables in portal-deploy.sh 
```
bash$  git clone ssh://lmistky@gerrit-gamma.gic.ericsson.se:29418/AIA/CI/infra && cd infra/portal-deploy
bash$  source <openstack credentials>
bash$ ./portal-deploy.sh -n portal-dev1
portal-dev1 <Floating IP>
```

After the system has been deployed the portal can be torn down and redployed using

```
portal-dev $ cd ~
portal-dev $ ./portal-teardown.sh
portal-dev $ ./portal-deploy.sh
```
## VM access

ssh \<Floating IP><br>
root/password<br>
centos/centos<br>
 <br>
Portal web page:<br>
http://\<Floating IP><br>

Gogs:<br>
http://\<Floating IP>:28080<br>
root/Passw0rd<br>

## Troubleshooting
Cloud init logs
```
portal-dev $ tail -f /var/log/cloud-init.log
```

Portal deploy logs:
```
portal-dev $ tail -f ~/portal-deploy.log
```


