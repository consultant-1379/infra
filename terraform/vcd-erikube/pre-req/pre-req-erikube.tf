# Configure the OpenStack Provider
provider "openstack" {
}

resource "openstack_images_image_v2" "erikube-image" {
  name   = "centos7-1708-erikube"
  image_source_url = "http://cloud.centos.org/centos/7/images/CentOS-7-x86_64-GenericCloud-1708.qcow2"
  container_format = "bare"
  disk_format = "qcow2"
}


resource "openstack_compute_flavor_v2" "erikube-flavor" {
  name  = "erikube-flavor"
  ram   = "${var.erikube-ram}"
  vcpus = "${var.erikube-vcpu}"
  disk  = "${var.erikube-disk}"
  is_public = "true"
}

resource "openstack_networking_secgroup_v2" "erikube-open" {
  name = "erikube-open"
  description = "Erikube open security group"
}

resource "openstack_networking_secgroup_rule_v2" "erikube_rule_1" {
  direction = "ingress"
  ethertype = "IPv4"
  protocol = "any"
  security_group_id = "${openstack_networking_secgroup_v2.erikube-open.id}"
  remote_ip_prefix = "0.0.0.0/0"
}

resource "openstack_compute_keypair_v2" "erikube-keypair" {
  name       = "erikube-keypair"
  public_key = "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQClrQtmM5yNXU6f/FNoWlEhgrDDF6grSXaKJyn4OspO72xmkXe5xAIMEN2gg7cw+P64arsSEu4AKQhzAejrZSr5p5z59ln9qMsPUXEcsSwY2o1nGwthhojkJocU2CfMvH0r/kuhc84slIvuU+B57Qb5fCTX5u5C0+KVkjTVsENuguTM9VxKQUygLlaIGf1KUF9yKZW2bizt9OP0Veur5lgNEkefZpyAV0NnEoD/CmCGeXMH/YF6Flb/0PBVW6uk8abrvSIM6iO2k/AjtQFtjkqxsMtAsnfZqSJUX0/SWZxUAs8G/P6jc4bKaQkmISEE1/bP0ZHo8WXoEDxyajEN1wWx Generated-by-Nova"
}

resource "openstack_networking_router_v2" "erikube-router" {
  name             = "erikube-router"
  external_gateway = "${var.external-network-id}"


}

resource "openstack_networking_network_v2" "erikube-net" {
  name           = "erikube-net"
  admin_state_up = "true"
}

resource "openstack_networking_subnet_v2" "erikube-subnet" {
  name       = "10.10.10.0"
  network_id = "${openstack_networking_network_v2.erikube-net.id}"
  cidr       = "10.10.10.0/24"
  ip_version = 4
  dns_nameservers = "${var.nameserver}"
}

resource "openstack_networking_router_interface_v2" "router_interface_internal" {
  router_id = "${openstack_networking_router_v2.erikube-router.id}"
  subnet_id = "${openstack_networking_subnet_v2.erikube-subnet.id}"
}