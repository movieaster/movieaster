MovieAster
==========

The Movie Manager for your private NAS

System Requirements
-------------------
  - PHP 5.3.2
  - MySQL

Installation
------------
### 1) Download Latest Version

    git clone git://github.com/movieaster/movieaster.git

or download and unzip:
https://github.com/movieaster/movieaster/archive/master.zip


### 2) Install the Vendor Libraries
Run the following:

    php bin/vendors install

Note that you **must** have git installed and be able to execute the `git`
command to execute this script. If you don't have git available, either install
it or download Symfony with the vendor libraries already included.

### 3) Copy to the webserver `htdocs` folder
  - Synology NAS: https://github.com/movieaster/movieaster/wiki/Install-on-Synology-NAS
  - Netgear NAS: https://github.com/movieaster/movieaster/wiki/Install-on-Netgear-NAS
  - QNAP NAS: https://github.com/movieaster/movieaster/wiki/Install-on-Qnap-NAS

### 4) Config MySQL
Config DB username/password: app/config/parameters.ini
Create DB a new DB `movieaster`:

    php app/console doctrine:database:create

Update schema (sql/movieaster.sql):

    php app/console doctrine:schema:update --force

