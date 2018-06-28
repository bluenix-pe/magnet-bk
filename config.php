<?php

/**
 * config.php
 * 
 * magnet-bk config file
 * 
 */

// Version
define("VERSION","1.0");

// Timezone
define("TIMEZONE","America/Lima");
@date_default_timezone_set(TIMEZONE);

// Day format
define("DAYFORMAT",date("Ymd_His"));

// Log names
define("LOGNAME","magnet-bk." . date("Ymd") . ".log");

/* Max retain log days */
define("MAX_DAYS",2);

/* Dir where magnet-bk is installed */
define("INSTALL_DIR", dirname(dirname(__DIR__)));

// Show details
define("VERBOSE",in_array("-nv",$argv) ? false: true);

// Don't ask questions
define("YESALL",in_array("-y",$argv) ? true: false);

// Show how to config mysql user
define("CONFIG_MYSQL",in_array("--config-mysql",$argv) ? true: false);

// No commpresion ?
define("NOZIP",in_array("-nz",$argv) ? true: false);
//$wserv      = in_array_ex($argv,"--server=")    ? true: false;
//$wdata      = in_array_ex($argv,"--databases=") ? true: false;

// Show Help
define("ARG_HELP",in_array("--help",$argv) ? true: false);

/* Config Server Farm */

/* Backup Path                            */
/* DON'T PUT SLASH AT THE END OF LINE     */
/* Example: "bpath" => "/backups"         */

$servers = array(

	// Server # 0
	0 => array("engine" => "mysql", "version" => "5",
		   "nombre" => "local", "bservidor" => "localhost", 
		   "bbase" => "mysql", "bpuerto" => "3306", 
		   "busuario" => "root", "bclave" => "123456" ,
		   "bpath" => "/root/bk","library" => "",
		   "bin" => "/usr/bin/mysqldump", 
		   "extra" => "--compress",
		   "logs" => "/root/bk/logs"),
);



?>
