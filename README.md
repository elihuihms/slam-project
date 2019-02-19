# Description

SLAM (SQL-based Laboratory Asset Management) is a web based application that helps research labs organize and maintain information on their key assets (plasmids, cell strains, purified protein lots, etc.). It was designed from the ground up to be intuitive to use and easy to modify in order to suit each lab’s unique needs.

SLAM's approach is to act as a thin veneer to a relational database. In this way, the database is used not only for storage of data, but also as the structure for what elements of data are critical for each asset type. This avoids vendor lock and and ensures that your data will always be easily retrievable, even by third party utilities.

# License

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License v2 as published by the Free Software Foundation and provided in the LICENSE.md file.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

SLAM includes code from the Adminer project (www.adminer.org), which is licensed under the Apache License 2.0 or GPL 2.

# Requirements

* PHP 7.0+
* MySQL 5.0+

# Installation

### Downloading a SLAM package

You can download a complete archive from the following locations, untar/unzip it onto your web server, and you're good to go:

* http://steelsnowflake.com/downloads/?t=slam-versions
* https://github.com/steelsnowflake/slam/archive/master.zip

### From GitHub

```
$ cd ~/webroot
$ git clone https://github.com/steelsnowflake/slam.git
```

Once SLAM has been saved to your server, navigate to yourserver.com/slam/install/index.php for a step-by-step, guided setup. Check out http://steelsnowflake.com/SLAM/installation for even more information.

### AWS Elastic Beanstalk

Installation to an Amazon Elastic Beanstalk is the recommended deployment option for most users. Select the preconfigured AWS PHP platform, upload a SLAM .zip archive to initialize the environment, add a MySQL RDS, and once the environment is stable, navigate to the environment's URL to complete installation.

If the database is added to the platform prior to running the installer, the environment's RDS connection and authentication settings will be provided as defaults during installation.

# Advanced

### Attached Files

It's recommended that the directory containing attached asset files (`archive_dir` in SLAM's configuration.ini) be located outside of the SLAM directory and even better, outside of the server's webroot in order to prevent direct access by web clients. (There is an .htaccess file provided with SLAM that should prevent this by default).

### PHP settings

You may wish to edit your php.ini in order to attach files larger than the 5MB default. Specifically, you will need to find and change the following options:

* `post_max_size = 100M`
* `upload_max_filesize = 100M`

### Updating

1. Ensure that the directory containing the attached archive files (`archive_dir` in SLAM's configuration.ini) is backed up and/or in a *SAFE PLACE* outside of the SLAM directory.
1. Copy SLAM's configuration.ini and preferences.ini file to a safe location.
1. Copy or `git pull` the new SLAM files to the SLAM web server directory.
1. Move the existing configuration.ini and preferences.ini files back into SLAM directory, and attempt to log in to SLAM. If SLAM needs to update these files it will do so automatically.






