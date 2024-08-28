
###########################
##   Define Variables
############################

### The numbers of kuberenttes workers to be deployed
variable "worker-count" {  default = "3" }
### Directory of erikube ansible scripts
variable "erikube-ansible-dir" { default = "~/eccd-1.0.0-64-da250a7" }
### Name of security group that will be used by master and workers
variable "erikube-securitygroup" {  default = "open" }
### Cloud image to be used for VMs
variable "erikube-image" {  default = "centos7-1708" }
### openstack flavors to be used by VMs
variable "erikube-flavor" { default = "aia.eccd.flavor" }
### openstack floating IP network
variable "external-network" { default = "nova_floating" }
### openstack floating IP network
variable "internal-network" { default = "aia-c3b1-internal" }
### openstack keypair to be used  
variable "erikube-keypair" { default = "eccd-keypair" }
### whether to run ansible eccd optional monitoring steps ( true|false ) 
variable "eccd-monitoring" { default = "false" }
### whether to run ansible custom posts steps ( true|false ) 
variable "post-steps" { default = "true" }

