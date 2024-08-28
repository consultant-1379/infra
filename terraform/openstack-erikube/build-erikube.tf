##############################
## Terraform script that will : 
##     - create openstack VMs 
##	   - Run erikube ansible scipts
##	   - Run custom post steps  
##############################

###########################
##  Create Erikube VMs
############################

# Define ansible eccd-cloud-config file
data "template_file" "cloud-config" {

    template = "${file("./eccd-cloud-config")}"
 }

### Use OpenStack Provider
provider "openstack" {
}

### Reserve an IP floating IP pool for master
resource "openstack_networking_floatingip_v2" "master-fip" {
  pool = "${var.external-network}"
}

### Create erikube master
resource "openstack_compute_instance_v2" "erikube-master" {
  count           = "1"
  name            = "${terraform.workspace}-master"
  image_name      = "${var.erikube-image}"
  flavor_name     = "${var.erikube-flavor}"
  key_pair        = "${var.erikube-keypair}"
  security_groups = ["${var.erikube-securitygroup}"]
  user_data	      = "${data.template_file.cloud-config.rendered}"
  network {
    name = "${var.internal-network}"
  }
}

### Create erikube workers
resource "openstack_compute_instance_v2" "erikube-worker" {
  count           = "${var.worker-count}"
  name            = "${terraform.workspace}-worker-${count.index + 1}"
  image_name      = "${var.erikube-image}"
  flavor_name     = "${var.erikube-flavor}"
  key_pair        = "${var.erikube-keypair}"
  security_groups = ["${var.erikube-securitygroup}"]
  user_data	      = "${data.template_file.cloud-config.rendered}"
  network {
       name = "${var.internal-network}"
  }

}

### Associate IP  erikube master
resource "openstack_compute_floatingip_associate_v2" "master-fip" {
  floating_ip = "${openstack_networking_floatingip_v2.master-fip.address}"
  instance_id = "${openstack_compute_instance_v2.erikube-master.id}"
  
}


### checks that cloud-init is completed  , 6 min timeout
resource "null_resource" "cloud-init-check" {
 provisioner "remote-exec" {
    inline = [
      "/bin/bash -c \"timeout 600 sed '/SUCCESS: running modules for final/q' <( sudo tail -f /var/log/cloud-init.log)\""
    ]
	
    connection {
    host = "${openstack_networking_floatingip_v2.master-fip.address}"
    type     = "ssh"
    user     = "centos"
    password = "secret"
     }
  }
}

### copy build ssh key to master
resource "null_resource" "copy-ssh-key" {
  depends_on = ["null_resource.cloud-init-check"]
    provisioner "file" {
    source      = "~/.ssh/id_rsa.pub"
    destination = "/tmp/build-key.pub"
  connection {
    host = "${openstack_networking_floatingip_v2.master-fip.address}"
    type     = "ssh"
    user     = "centos"
    password = "secret"
     }
  }
}
 
###  Clear Local known hosts
resource "null_resource" "clear-local-known_hosts" {
  depends_on = ["null_resource.cloud-init-check"]
 provisioner "local-exec" {
     command =  "echo > ~/.ssh/known_hosts " 
  }
}

### add build key to authorized_hosts
resource "null_resource" "setup-passwordless-ssh" {
 depends_on = ["null_resource.copy-ssh-key"]
 provisioner "remote-exec" {
 
    inline = [
      "/bin/bash -c \"cat /tmp/build-key.pub >>  ~/.ssh/authorized_keys;rm /tmp/build-key.pub \""
    ]
    connection {
    host = "${openstack_networking_floatingip_v2.master-fip.address}"
    type     = "ssh"
    user     = "centos"
    password = "secret"
     }
  }
 }

###########################
##  Run Erikube Ansible 
############################

# Define ansible inventory file
data "template_file" "inventory_file" {

    template = "${file("./inventory.template")}"
    depends_on = ["null_resource.cloud-init-check"]

      vars {
        master_fip = "${openstack_networking_floatingip_v2.master-fip.0.address}"
        worker_ip_list = "${join("\n",formatlist("%s",openstack_compute_instance_v2.erikube-worker.*.access_ip_v4))}"
      }
}

# Create ansible inventory file
resource "null_resource" "create-inventory" {
 depends_on = ["null_resource.cloud-init-check"]
   provisioner "local-exec" {
     command =  "echo \"${ data.template_file.inventory_file.rendered }\" > ./terraform.tfstate.d/${terraform.workspace}/inventory.${terraform.workspace} " 

    }
	
    provisioner "local-exec" {
     command =  "sleep 90"
    }
} 

#################################
# Run ansible erikube playbook
#################################
resource "null_resource" "ansible-run" {

    depends_on = ["null_resource.create-inventory"]
    provisioner "local-exec" {
     command =  " (  cd ${var.erikube-ansible-dir}/ansible/erikube  &&  ANSIBLE_SSH_ARGS= ANSIBLE_HOST_KEY_CHECKING=False ANSIBLE_GATHERING=implicit ANSIBLE_CONFIG=${var.erikube-ansible-dir}/ansible/erikube/ansible.cfg ansible-playbook -i ${path.root}/terraform.tfstate.d/${terraform.workspace}/inventory.${terraform.workspace} ${var.erikube-ansible-dir}/ansible/erikube/install.yml   )"
    }
} 

#################################
##  Run Erikube optional monitoring 
#################################
resource "null_resource" "ansible-eccd-monitoring" {
    count = "${var.eccd-monitoring == "true" ? 1 : 0}" 
    depends_on = ["null_resource.ansible-run"]
    provisioner "local-exec" {
     command =  "ANSIBLE_SSH_ARGS=  ANSIBLE_HOST_KEY_CHECKING=False ANSIBLE_CONFIG=${var.erikube-ansible-dir}/ansible/erikube/ansible.cfg ansible-playbook  -i ./terraform.tfstate.d/${terraform.workspace}/inventory.${terraform.workspace}  ${var.erikube-ansible-dir}/ansible/erikube/playbooks/prometheus-grafana-deploy.yml"
    }
}


#################################
##  Run Erikube custom post steps 
#################################
resource "null_resource" "ansible-post" {
    count = "${var.post-steps == "true" ? 1 : 0}" 
    depends_on = ["null_resource.ansible-run"]
    provisioner "local-exec" {
     command =  "ANSIBLE_SSH_ARGS=  ANSIBLE_HOST_KEY_CHECKING=False ANSIBLE_CONFIG=${var.erikube-ansible-dir}/ansible/erikube/ansible.cfg ansible-playbook  -i ./terraform.tfstate.d/${terraform.workspace}/inventory.${terraform.workspace}  ${path.root}/erikube-post/custom-post.yaml"
    }
}

