###########################
##   Define Variables
############################

### VCD User
variable "vcd_user" {  default = "kubebot" }
### VCD Password
variable "vcd_pass" {  default = "kubebot" }
### VCD org
variable "vcd_org" {  default = "GTEC-ORG" }
### VCD url
variable "vcd_url" {  default = "https://atvpivcd18-v6.gtoss.eng.ericsson.se/org/GTEC_Org/" }
### VCD vdc
variable "vcd_vdc" {  default = "GTEC_Org" }
### Template name
variable "vapp_template" {  default = "ERIKUBE-HA-BLANK" }
### vApp Catalog
variable "vapp_catalog" {  default = "GTEC-ORG" }
