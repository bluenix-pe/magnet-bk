# [magnet-bk](https://bluenix.pe/tools/scripting/magnet-bk.html)
![version](https://img.shields.io/badge/version-1.3.0-blue.svg) ![license](https://img.shields.io/badge/license-MIT-green.svg)

magnet-bk is an small and fast tool that wraps official backup utilities from database providers (like mysqldump) in order to make easy local or remote backups in just one place.

## Features

* JOBF+CF Solution (Just One ¿Big? File + Config File) less than 20k
* Allows you to configure remote backups for N servers
* Allows you to see a nice progress task
* Records all events and errors in an activity log
* Allows you to set the maximum backup retention period 
* It works in 2 modes: interactive and unattended
* Allows you to configure backup compression
* Allows you to configure backups for very old database versions (for example: mysql 4)

## Requirements
* PHP 5.0 or superior
* MySQL Tools 5.0 or superior
* php-mysql + readline libraries
* Database user with proper rights to make backups (see  --config-mysql)

## Quick Start

1. git clone https://github.com/bluenix-pe/magnet-bk.git
2. configure config.php file
3. chmod +x magnet.bk.php
4. ./magnet-bk.php

Note: Use **./magnet-bk.php --config-mysql** to see how to create remote db users.

## Example

<img src="http://www.bluenix.pe/tools/scripting/magnet-bk/magnet-bk1.png" />
