#!/usr/bin/bash

## VARIABLES
PORTAL_FLAVOR_ID="portal.flavor"
PORTAL_IMAGE_ID="CentOS-7-1708"
SSH_KEY_NAME="portal-keypair"
SECURITY_GROUP_NAME="open"
PRIVATE_NETWORK_NAME="erikube-portal-net"
PUBLIC_NETWORK_NAME="admin_floating_net"
PORTAL_CLOUD_INIT="portal-cloud-config.yml"

## display usage message
usage_msg()
{
        echo "Usage: $0 -n < portal VM name> "
        exit 1
}

## grab options from command line
while getopts "n:" arg
do
    case $arg in
    n) INSTANCE_NAME="$OPTARG"
     ;;
    \?) usage_msg
        exit 1
           ;;
    esac
done


## get the network IDs from network names
PORTAL_PRIVATE_NETWORK_ID=$(openstack network list -f value | grep $PRIVATE_NETWORK_NAME | cut -d' ' -f1| head -1)
PORTAL_PUBLIC_NETWORK_ID=$(openstack network list -f value | grep $PUBLIC_NETWORK_NAME| cut -d' ' -f1| head -1)

##  create VM with the cloud-init
openstack server create \
--flavor "$PORTAL_FLAVOR_ID" \
--image "$PORTAL_IMAGE_ID" \
--key-name "$SSH_KEY_NAME" \
--security-group "$SECURITY_GROUP_NAME" \
--nic "net-id=$PORTAL_PRIVATE_NETWORK_ID" \
--user-data  "$PORTAL_CLOUD_INIT" "$INSTANCE_NAME" 

##  get VM creation status
INSTANCE_STATUS=$( openstack server show "$INSTANCE_NAME" -f value -c status| tr -d '\r' )


##  wait until the VM creation is finished
until [ "$INSTANCE_STATUS" == 'ACTIVE' ]
do
    INSTANCE_STATUS=$( openstack server show "$INSTANCE_NAME" -f value -c status| tr -d '\r' )
    sleep 2;
done

##  wait until the VM creation is finished
PORTAL_FLOATING_IP_ID=$( openstack floating ip  list -f value --project $OS_PROJECT_NAME| grep None| cut -d' ' -f 2 | head -1| tr -d '\r')
if [ -z "$PORTAL_FLOATING_IP_ID" ]; then
    openstack ip floating  create "$PORTAL_PUBLIC_NETWORK_ID"
    PORTAL_FLOATING_IP_ID=$( openstack floating ip  list -f value --project $OS_PROJECT_NAME| grep None| cut -d' ' -f 2 | head -1| tr -d '\r')
fi

PORTAL_PUBLIC_IP=$( openstack floating ip show "$PORTAL_FLOATING_IP_ID" -f value -c floating_ip_address )

openstack ip floating add "$PORTAL_PUBLIC_IP" "$INSTANCE_NAME" 
echo ""
echo ""
echo "$INSTANCE_NAME $PORTAL_PUBLIC_IP"
echo ""
