<?php

/**
 * config.php
 * 
 * magnet-bk config file
 * 
 */

// Version
define("VERSION","1.3.0");

// Language
define("LANG","eng");

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
		   "name" => "remote_server", "bserver" => "localhost", 
		   "bbase" => "mysql", "bport" => "3306", 
		   "buser" => "root", "bpass" => "123456" ,
		   "bpath" => "/root/bk","library" => "",
		   "bin" => "/usr/bin/mysqldump", 
		   "extra" => "--compress",
		   "logs" => "/root/bk/logs"),
);

// English Language (eng)
$config['eng']['bad'] = "unkown topic";
$config['eng']['config_miss'] = "Fatal Error: config.php file miss";
$config['eng']['genbk'] = "Making Backups";
$config['eng']['del_old_bk'] = "[Deleting Old Backups]";
$config['eng']['bk_ini'] = "Make Backup for database";
$config['eng']['bk_error'] = "[ ERROR ] (More details in {1})";
$config['eng']['accept'] = "Accept (Y/N):";
$config['eng']['resume_title'] = "[Task Resume - Server '{1}']";
$config['eng']['resume_num'] = "* Make {1} database backups from '{2}' server";
$config['eng']['resume_user1'] = "* The database user";
$config['eng']['resume_user2'] = "will be used to make the backup";
$config['eng']['resume_bpath1'] = "* The backups will be saved in the folder";
$config['eng']['resume_bpath2'] = "from this server";
$config['eng']['resume_lpath1'] = "* The Logs will be saved in the folder";
$config['eng']['resume_lpath2'] = "from this server";
$config['eng']['resume_days1'] = "* Backups older than";
$config['eng']['resume_days2'] = "days will be deleted";
$config['eng']['resume_lib'] = "* This environment variable will be temporarily set: \n  {1}";
$config['eng']['resume_nzip'] = "* The backup will not be compressed with gzip";
$config['eng']['resume_mode'] = "* The script is running in unattended mode";
$config['eng']['mysql_err_cnn'] = "An error occurred while trying to access the databases on the '{1}' server";

// Spanish Language (spa)
$config['spa']['bad'] = "topico desconocido";
$config['spa']['config_miss'] = "Error Fatal: falta el archivo config.php";
$config['spa']['genbk'] = "[Generando Backups]";
$config['spa']['del_old_bk'] = "[Eliminando Backups Antiguos]";
$config['spa']['bk_ini'] = "Generando Backup para la Base de Datos";
$config['spa']['bk_error'] = "[ ERROR ] (Mas detalles en {1})";
$config['spa']['accept'] = "Aceptar (Y/N):";
$config['spa']['resume_title'] = "[Resumen de Tareas - Servidor '{1}']";
$config['spa']['resume_num'] = "* Sacar backups de {1} bases de datos del servidor '{2}'";
$config['spa']['resume_user1'] = "* Se utilizara el usuario de base de datos";
$config['spa']['resume_user2'] = "para generar el backup";
$config['spa']['resume_bpath1'] = "* Los backups se guardaran en la carpeta";
$config['spa']['resume_bpath2'] = "de este servidor";
$config['spa']['resume_lpath1'] = "* Los Logs seran guardados en la carpeta";
$config['spa']['resume_lpath2'] = "de este servidor";
$config['spa']['resume_days1'] = "* Se eliminaran los backups con mas de";
$config['spa']['resume_days2'] = "dias de antiguedad";
$config['spa']['resume_lib'] = "* Se seteara temporalmente la siguiente variable de entorno: \n  {1}";
$config['spa']['resume_nzip'] = "* El backup no sera comprimido con gzip";
$config['spa']['resume_mode'] = "* El script se esta ejecutando en modo desatendido";
$config['spa']['mysql_err_cnn'] = "Ocurrio un error al intentar acceder a las base de datos en '{1}'";

?>
