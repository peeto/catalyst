# Catalyst PHP tasks

Repository for work on Catalyst PHP competency test.

## 1. Script Task

Firstly, this task can fail due to the database and an appropriate user not existing in the MySQL instance. user_upload.sql has been created to address this. It contains variables for hostname, database, username and password that can be modified as needed. The script can be ran as follows:

```
mysql -u root -p < src/user_upload.sql
```

The Ubuntu server should have the mysqli extension installed, if not the following will help:

In BASH:
```
src/install.sh
```

Add to php.ini:
```
extension=php_mysqli.so
```

In BASH:
```
sudo phpenmod mysqli
```

### IMPORTANT

The user_upload.php script requires a 'd' parameter to be set that denotes the name of the database. This is to make it easier to test multiple applications at once. See src/user_upload.sql


To run this from the repository root:
```
php -f src/user_upload.php -- --help
```

## 2. Logic Test

This was done as a single commit because the separator generation and modulo functions were thought out before hand. A class was used to show the breakdown in logic to achieve the required outcome. One issue with my approach is it makes it difficult to modify the min (1) and max (100) requirements as the separator generator would need to be aware of the minimum value. This I would solve by instantaing the class and storing the min and max as properties.

To run this from the repository root:

```
php -f src/foorbar.php
```

