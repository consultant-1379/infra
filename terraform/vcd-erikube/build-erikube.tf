##############################
## Terraform script that will : 
##     - Deploy VCD vApp
##	   - Run erikube ansible scipts
##	   - Run custom post steps  
##############################



###########################
##  Create Erikube VMs
############################

### Use VCD Provider
provider "vcd" {
  user                 = "${var.vcd_user}"
  password             = "${var.vcd_pass}"
  org                  = "${var.vcd_org}"
  url                  = "${var.vcd_url}"
  vdc                  = "${var.vcd_vdc}"
  allow_unverified_ssl = "True"
}

resource "vcd_vapp" "erikube-ha-vapp" {
    name          = "ERIKUBE-TEST"
    catalog_name  = "var.vapp_catalog"
    template_name = "var.vapp_template"
}