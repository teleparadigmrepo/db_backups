mkdir -p /external_storage/Rammohan/db_backups/facelogin

#this  script is for mysql backup. Please make sure your username and password matches with the command given below
docker exec php74-apache2-mysql57_mysql_1 /usr/bin/mysqldump -uroot -pPassword123$  facelogin > /external_storage/Rammohan/db_backups/facelogin/facelogin_`date +"%d-%m-%Y:%H"`h.sql

zip -r /external_storage/Rammohan/db_backups/facelogin/facelogin_`date +"%d-%m-%Y:%H"`h.zip /external_storage/Rammohan/db_backups/facelogin/facelogin_`date +"%d-%m-%Y:%H"`h.sql

ls -d -1tr /external_storage/Rammohan/db_backups/facelogin/* | head -n -28 | xargs -d '\n' rm -rf

