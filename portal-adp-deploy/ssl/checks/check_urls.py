import requests
import sys
import time

class Check:

	def __init__(self, base, secure):
		self.base=base.rstrip("/")
		self.secure=secure

	def check_url(self, endpoint):
		url = self._build_url(endpoint)
		print "Trying {0}".format(url)
		try:
			res = requests.get(url, verify=self.secure)
			print res.status_code
		except:
			print "Error calling {0}".format(url)

	def _build_url(self, endpoint):
		return "{0}/{1}".format(self.base, endpoint.lstrip("/"))

def main(endpoint_file, base, verify=""):
	if verify:
		secure=True
	else:
		secure=False
	with open(endpoint_file) as epf:
		endpoints = [e.strip() for e in epf.readlines() ]
	print "Checking the following endpoints on {0}".format(base)
	print endpoints
	c = Check(base, secure)
	for endpoint in endpoints:
		c.check_url(endpoint)
		time.sleep(3)

if __name__ == '__main__':
	main(*sys.argv[1:])