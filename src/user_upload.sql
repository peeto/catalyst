/*
 SQL script to create schema and user for user_upload
 */

SET @username = "catalysttest";
SET @password = "catalysttest";
SET @hostname = "localhost";
SET @database = "user_upload";

START TRANSACTION;

SET @dbQueryText = CONCAT 
('
   CREATE DATABASE IF NOT EXISTS ', @database, ';'
);

PREPARE dbQuery FROM @dbQueryText;
EXECUTE dbQuery;

DEALLOCATE PREPARE dbQuery;

SET @userQueryText = CONCAT 
('
   CREATE USER "', @username, '"@"', @hostname, '" IDENTIFIED BY "', @password, '";'
);

PREPARE userQuery FROM @userQueryText;
EXECUTE userQuery;

DEALLOCATE PREPARE userQuery;

SET @userPrivText = CONCAT
('
   GRANT ALL ON ', @database, '.* TO "', @username, '"@"', @hostname, '";'
);


PREPARE userPrivQuery FROM @userPrivText;
EXECUTE userPrivQuery;

DEALLOCATE PREPARE userPrivQuery;

FLUSH PRIVILEGES;

COMMIT;
