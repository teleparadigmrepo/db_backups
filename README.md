## Cron Setup for DB Backups & S3 Upload
- delete any previous backups in the folder facelogin
- Edit paths and credentials in:
    - db_backups/dbscript.sh
   	- update path 'fullpath/db_backups/facelogin'
   	- update container name php74-apache2-mysql57_mysql_1 if db is running in container
   	- remove docker exec php74-apache2-mysql57_mysql_1  if it is not running in container
   	- update db details : root and Password123$ and facelogin 
   	
    - db_backups/DBbackupS3/AWSphpClient.php
	- replace 'awsapikey'
	- replace 'secretkey'
    	- update path 'fullpath/db_backups/facelogin'
	- update bucketname 'facelogindb-backup'
	
    - db_backups/DBbackupS3/php/config.xml (log path)
	- update path   /external_storage/Rammohan/db_backups/DBbackupS3/logs/s3/myLog.log
- Ensure log path is writable.
- Add to root crontab:
```bash
22 10 * * * bash fullpath/db_backups/dbscript.sh >> fullpath/db_backups/facelogin/cronlog.txt 2>&1

23 10 * * * /usr/bin/php -f fullpath/db_backups/DBbackupS3/AWSphpClient.php >> fullpath/db_backups/facelogin/cronlog.txt 2>&1
```

