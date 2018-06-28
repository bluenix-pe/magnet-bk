# magnet-bk
![version](https://img.shields.io/badge/version-1.0.0-blue.svg) ![license](https://img.shields.io/badge/license-MIT-green.svg)

magnet-bk es una pequeña y rápida herramienta que envuelve las utilidades de backup oficiales de los proveedores de bases de datos (como mysqldump) para realizar backups locales o remotos en un solo lugar.

## Características

* Solución JOBF+CF (Just One ¿Big? File + Config File) menor de 20k
* Permite configurar backups remotos para N servidores
* Permite ver el progreso de las tareas de backups
* Registra todos los eventos y errores en un log de actividad
* Permite configurar el periodo de retención de backups
* Funciona en 2 modos: interactivo y desatendido 
* Permite configurar la compresion del backup
* Permite configurar backups de versiones muy antiguas de bd (por ejemplo: mysql 4)

## Requerimientos
* PHP 5.0 o superior
* MySQL Tools 5.0 o superior
* Librerias php-mysql + readline
* Usuario de base de datos con permisos para realizar backups (ver opción --config-mysql)

## Primeros pasos

1. git clone https://github.com/bluenix-pe/magnet-bk.git
2. configurar el archivo config.php
3. chmod +x magnet.bk.php
4. ./magnet-bk.php

Nota: Usar **./magnet-bk.php --config-mysql** para ver los pasos para crear usuarios de base de datos remotos.

## Ejemplo de uso

<img src="http://www.bluenix.pe/tools/scripting/magnet-bk/magnet-bk.png" />
