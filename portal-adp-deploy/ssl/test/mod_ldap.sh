docker exec -it mock-ldap ldapmodify -x -H ldap://localhost:389 -D "cn=admin,dc=example,dc=org" -w admin -f /home/test/test.ldif -c


