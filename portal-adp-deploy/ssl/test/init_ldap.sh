docker exec -it mock-ldap ldapmodify -Y EXTERNAL -H ldapi:/// -f /home/test/attributes.ldif 
docker exec -it mock-ldap ldapadd -x -H ldap://localhost:389 -D "cn=admin,dc=example,dc=org" -w admin -f /home/test/test.ldif -c


