There are three ways to install Sphider-Plus:
  * Install from scratch
  * Upgrade from original Sphider to Sphider-Plus
  * Update to new Sphider-plus releases

In order to get correct function of Sphider-Plus, please follow the instructions as described below.

---

# THE BELOW INFO IS STILL BEING FORMATTED #
### Install from scratch ###
  1. Unpack the files, and copy them to the server, for example to: <b>C:\programs\xampp\htdocs\public\sphider-plus\</b>
  1. Create a database in MySQL to hold Sphider-Plus data. Collation of the database must be UTF8-bin
    1. at command prompt type (to log into MySQL): `mysql -u <your username> -p` _Enter your password when prompted._
    1. in MySQL, type: `CREATE DATABASE sphider-plus_db;` _Of course you can use some other name for database instead of sphider-plus\_db._
    1. Use exit to exit MySQL.
  1. In .../sphider-plus/settings/ directory, edit the file database.php and change:
$database
$mysql\_user
$mysql\_password
$mysql\_host
to your personal requirements to correct values (if you don't know what $mysql\_host should be, it should
probably stay as it is - 'localhost').
  1. Open the file .../admin/install\_all.php in your browser. This script will create the tables necessary for
Sphider-plus to operate.
(http://localhost/public/sphider-plus/admin/install_all.php)
  1. In admin directory, edit the file auth.php to change the administrator user name and password _(Default values are 'admin' and 'admin')_
  1. Open admin.php in your browser and start indexing
(http://localhost/public/sphider-plus/admin/admin.php)
Installing from scratch your site table is empty. You will be asked to add any URL to be indexed.
You should enter something like:
http://www.abcd.de
  1. search.php is the default search script
(http://localhost/public/sphider-plus/search.php)


---

### Upgrade from original Sphider to Sphider-Plus ###


---

### Updating to new Sphider-Plus releases ###