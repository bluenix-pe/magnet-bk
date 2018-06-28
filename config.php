<?php

/**
 * config.php
 * 
 * Archivo de configuracion donde guarda los datos de conexion a la
 * base de datos MySQL y del generador de Backups
 * 
 */

// Version
define("VERSION","1.0");

// Timezone
define("TIMEZONE","America/Lima");
@date_default_timezone_set(TIMEZONE);

// Formato de Fecha
define("DAYFORMAT",date("Ymd_His"));

// Log names
define("LOGNAME","magnet-bk." . date("Ymd") . ".log");

/* Periodo Maximo de Retencion de Backups */
define("MAX_DAYS",2);

/* Directorio donde esta instalado magnet-bk */
define("INSTALL_DIR", dirname(dirname(__DIR__)));

// Mostrar detalles
define("VERBOSE",in_array("-nv",$argv) ? false: true);

// Atencion desatendida
define("YESALL",in_array("-y",$argv) ? true: false);

// SOlicita configuracion de usuario MySQL
define("CONFIG_MYSQL",in_array("--config-mysql",$argv) ? true: false);

// Sin Compresion
define("NOZIP",in_array("-nz",$argv) ? true: false);
//$wserv      = in_array_ex($argv,"--server=")    ? true: false;
//$wdata      = in_array_ex($argv,"--databases=") ? true: false;

// Mostrar Ayuda
define("ARG_HELP",in_array("--help",$argv) ? true: false);

/* Configuracion de Granja de Servidores */

/* Path del Backup                        */
/* NO INCLUIR SLASH AL FINAL              */
/* Ejemplo: "bpath" => "/backups"         */

$servers = array(

	// Servidor Nro 0
	0 => array("engine" => "mysql", "version" => "5",
		   "nombre" => "local", "bservidor" => "localhost", 
		   "bbase" => "mysql", "bpuerto" => "3306", 
		   "busuario" => "backup", "bclave" => "mi-clave" ,
		   "bpath" => "/root/bk","library" => "",
		   "bin" => "/usr/bin/mysqldump", 
		   "extra" => "--compress",
		   "logs" => "/root/bk/logs"),
);



?>
