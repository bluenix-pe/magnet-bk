#!/usr/bin/php
<?php
/*********************************************************************
*
* magnet-bk v1.0 - Remote Backup Facilitator
*
* Config File : config.php
*
* How to use  : magnet-bk.php [-nv][-y][-nz][--config-mysql]
* Help        : magnet-bk.php --help
* Author      : Fernando Diaz Sanchez <sirfids@gmail.com>
* Date        : 2018/Jun/25
* Modified    : 2018/Jun/28
* Requirement : PHP 5.0+, readline, mysql tools, db admin user
*
********************************************************************/

if (file_exists("config.php")){
	include_once("config.php");
}else{
	echo "Error Fatal: falta el archivo config.php\n";
	exit(1);
}

// Show Help ?
if ( ARG_HELP ){ show_help(); exit(0);}

// Show config mysql user ?
if ( CONFIG_MYSQL ) { mysql_show_config(); exit(0);}

// Process Server List
backup_servers($servers);

function backup_servers($lista){

	// 1: Check type of server
	foreach($lista as $srv)
	{
		switch($srv['engine']){
		case "mysql": mysql_backup_server($srv);break;
		}
	}
}

function mysql_backup_server($srv){	

	// Setting config server variables
	$strAlias     = $srv['name'];
	$strBServer   = $srv['bserver'];
	$strBPort     = $srv['bport'];
	$strBBase     = $srv['bbase'];
	$strBUser     = $srv['buser'];
	$strBPass     = $srv['bpass'];
	$strBPathBK   = $srv['bpath'];
	$strVersion   = $srv['version'];
	$strBinary    = $srv['bin'];
	$strPathLog   = $srv['logs'];
	$strFullPathLog = $strPathLog . "/" . LOGNAME;

	$intNroDB     = 0;
	$hasProc      = false;
	$strLog       = "";
	$i 	      = 0;

	$rstDatabases = mysql_get_databases($strBServer,$strBBase,
				$strBPort,$strBUser,
				$strBPass);
	if ($rstDatabases == false){ return false;}

	$intNroDB = mysql_count_databases($rstDatabases);
	
	// Check if lib variables are set
	$strLibrary = "";
	if ($srv['library'] != ""){
		$strLibrary = "LD_LIBRARY_PATH=" . $srv['library'] .
				":/lib:/usr/lib/mysql";
		// Set ENV variables
		putenv($strLibrary);
	}

	if (VERBOSE){
		mysql_show_resume($strAlias,$intNroDB,$strBServer,
				$strVersion,$strBPathBK,$strPathLog,
				$strLibrary,$strBUser);
	}

	if (confirm_backup()==false){ return false; }

	if ( VERBOSE )
		echo colorStr("\n[Generando Backups]\n\n","cyan");

	// Loop all databases except *_schema
    foreach($rstDatabases as $vDB)
	{
		mysql_backup_database($srv,$vDB,$i);
		$i++;
    } // End Loop databases
	
	delete_old_backups($strBPathBK,$strFullPathLog);

} // End backup_servers

function delete_old_backups($PathBackup,$strFullPathLog){
	if ( VERBOSE )
		echo colorStr("\n[Eliminando Backups Antiguos]\n\n","cyan");
	// Delete old backups
	$strCmd = "find $PathBackup/ -mtime +" . MAX_DAYS . 
		  " -name '*.gz' -exec rm -rf {} \;";
	$strLog =  date("Ymd H:i:s : ") . $strCmd . "\n";
	@error_log( $strLog, 3, $strFullPathLog );
	@system($strCmd);
}

function mysql_backup_database($srv,$vDB,$i){
	
	// Setting config server variables
	$strAlias     = $srv['name'];
	$strBPort     = $srv['bport'];
	$strBBase     = $srv['bbase'];
	$strBUser     = $srv['buser'];
	$strBPass     = $srv['bpass'];
	$strVersion   = $srv['version'];
	$strBinary    = $srv['bin'];
	$strPathLog   = $srv['logs'];
	$strExtraParam  = $srv['extra'];
	$strFullPathLog = $strPathLog . "/" . LOGNAME;
	$hasProc = true;

	// Prepare DB variables
	$strDBName     = $vDB['Database'];
	$PathBackup      = $srv['bpath'];
	$strBackupName = $PathBackup . "/" . $strDBName . 
				"." . DAYFORMAT;
	$strErrorFile  = $strPathLog . "/" . $strDBName . 
				".$i.err";
	$strIP           = $srv['bserver'];
	$result          = false;
	$strWithProc     = "";
	$strWithTrig     = "";

	if ( $strDBName != "information_schema" &&
		$strDBName != "performance_schema"){

		$strCmd = mysql_get_cmd($hasProc,$strVersion,$strBinary,
				$strBUser,$strBPass,$strIP,
				$strDBName,$strBackupName,
				$strErrorFile,$strFullPathLog,
				$strExtraParam);

		if ( VERBOSE ){
			echo colorStr("Generando Backup para ".
					"la Base de Datos ","white");
			echo colorStr($strDBName,"cyan");
		}

		$result = mysql_exec_backup($strCmd,$strErrorFile,
			$strDBName,$strFullPathLog,$strBackupName);
		
	}

	return $result;

}

function mysql_exec_backup($strCmd,$strErrorFile,$strDBName,
	$strFullPathLog,$strBackupName){
	
	$hasError = false;
	// Execute OS Command
	@system($strCmd);

	// If there was errors
	if ( file_exists($strErrorFile) && 
		strlen(file_get_contents($strErrorFile)) > 0)
	{
		$strError = str_pad("[ ERROR ] (Mas detalles en ".
				   "$strErrorFile)", 
				   30 - strlen($strDBName)," ",
				   STR_PAD_LEFT) . "\n";
		if ( VERBOSE ) echo colorStr($strError,"red");
		$hasError = true;
		$strLog =  DAYFORMAT . " : " . $strError . "\n";
		@error_log( $strLog, 3, $strFullPathLog );
	}
	else  // Otherwise
	{
		$strOK = str_pad("[  OK  ]", 30 - strlen($strDBName),
				" ",STR_PAD_LEFT) . "\n";
		if ( VERBOSE ) echo colorStr($strOK,"green");

		// Zip the Database
		if ( !NOZIP ){
			$strZip = "/bin/gzip $strBackupName";
			@system($strZip);
			$strLog =  DAYFORMAT . " : " . $strZip . "\n";
			@error_log( $strLog, 3, $strFullPathLog );
		}
	}
	sleep(1);
	if ( !$hasError ){
		// Delete error file
		@system("rm $strErrorFile");
		return true;
	}else{
		return false;
	}
}

function mysql_get_cmd($hasProc,$strVersion,$strBinary,$strBUser,
	$strBPass,$strIP,$strDBName,$strBackupName,
	$strErrorFile,$strFullPathLog,$strExtraParam){
	
	$strWithProc = ( $hasProc ) ? "--routines " : "";
	$strWithTrig = ( $strVersion == "5" ) ? "--triggers " : "";

	$strCmd  = "$strBinary --add-drop-database -a ".
		   "$strWithTrig $strWithProc $strExtraParam " .
		   "-u " . $strBUser . " -p" . $strBPass . 
		   " -h $strIP --databases $strDBName > " .
		   "$strBackupName 2>$strErrorFile";

	$strLogCmd = "$strBinary --add-drop-database -a ".
		     "$strWithTrig $strWithProc $strExtraParam " .
		     "-u " . $strBUser . " -p" . 
		     " -h $strIP --databases $strDBName " .
		     " > $strBackupName 2>$strErrorFile";

	$strLog = date("Ymd H:i:s : ") . $strLogCmd . "\n";
	@error_log( $strLog, 3, $strFullPathLog );

	return $strCmd;
}

function confirm_backup(){

	$rpta = false;

	if ( YESALL == false )
	{
		// Ask confirmation
		while( $rpta != "Y" && $rpta != "N" )
		{
			$rpta = strtoupper(readline("Aceptar (Y/N):"));
		}
	
		if ( $rpta == "N" ) return false;

	}

	return true;
}

function mysql_show_resume($strAlias,$intNroDB,$strBServer,$strVersion,
	$strBPathBK,$strPathLog,$strLibrary,$strBUser){
	
	echo colorStr("\n[Resumen de Tareas - Servidor '".
			$strAlias."']\n","cyan");
	echo colorStr("\n* Sacar backups de " .
			"$intNroDB base de datos del servidor '" . 
			$strBServer . "'\n","white");
	echo colorStr("* Se utilizara el usuario mysql ","white");
	echo colorStr("'$strBUser'","yellow");
	echo colorStr(" para generar el backup\n","white");
	echo colorStr("* Los backups se guardaran en la carpeta '","white");
	echo colorStr($strBPathBK,"yellow");
	echo colorStr("' de este servidor\n","white");
	echo colorStr("* Los Logs seran guardados en '","white");
	echo colorStr($strPathLog,"yellow");
	echo colorStr("' de este servidor\n","white");
	echo colorStr("* Se eliminaran los backups con mas de ","white");
	echo colorStr(MAX_DAYS,"yellow");
	echo colorStr(" dias de antiguedad\n","white");
	if ($strLibrary != ""){
		echo colorStr("* Se seteara temporalmente la siguiente ".
			"variable de entorno: \n  $strLibrary\n\n","white");
	}
	if (NOZIP) {echo colorStr("* El backup no sera comprimido ".
			"con gzip\n","yellow");}
	if (YESALL){echo colorStr("* El script se esta ejecutando " .
			"en modo desatendido\n","yellow");}
}

function mysql_count_databases($rstDatabases){

	$intNroDB = 0;
	// Loop all DB
	foreach($rstDatabases as $vDB)
	{
		// Counting databases except *_schema
		if ( $vDB['Database'] != "information_schema" &&
			$vDB['Database'] != "performance_schema"){ 
			$intNroDB++;
		}
	}

	return $intNroDB;
}

function mysql_get_databases($strBServer,$strBBase,
	$strBPort,$strBUser,$strBPass){

	// DB Connection and get all databases
	try{
		$clsBase = new mysql_cli($strBServer,$strBBase,
					$strBPort,$strBUser,
					$strBPass);
		$rstDatabases = NULL;
		$rstDatabases = $clsBase->get_databases();
		unset($clsBase);
	}
	catch(exception $ex)
	{
		echo colorStr("\nOcurrio un error al intentar acceder " .
				"a las base de datos en '" . $strBServer .
				"'\n","lred");
		echo colorStr("Error : " . $ex->getMessage() .  
				"\n\n","lred");
		$strLog = date("Ymd H:i:s : ") . "Error : " . 
				$ex->getMessage() . "\n";
		@error_log( $strLog , $strFullPathLog );
		return false;
	}

	return $rstDatabases;
}

// Returns boolean if $strText are found in $arr
function in_array_ex($arr,$strText)
{
	foreach($arr as $val)
	{
		$nro = strpos($val,$strText);
		if ( gettype($nro) == 'integer' )
		{
			return true;
		}
	}
	return false;
}

function extraer_argv_ex($arr,$strParam)
{
	foreach($arr as $val)
	{
		$nro = strpos($val,$strParam);
		if ( gettype($nro) == 'integer' )
		{
			$rst = explode(",", substr($val,strlen($strParam) ) );
			return ( count($rst)>1 ) ? $rst : $rst[0];
		}
	}
	return false;
}

// Returns colored string
function colorStr($string, $fgc = null, 
			$bgc = null) {
	// Text Colors
	$fg_color['black']	= '0;30'; $fg_color['dark_gray']= '1;30';
	$fg_color['blue']	= '0;34'; $fg_color['lblue']	= '1;34';
	$fg_color['green']	= '0;32'; $fg_color['lgreen']	= '1;32';
	$fg_color['cyan']	= '0;36'; $fg_color['lcyan']	= '1;36';
	$fg_color['red']	= '0;31'; $fg_color['lred']	= '1;31';
	$fg_color['purple']	= '0;35'; $fg_color['lpurple']	= '1;35';
	$fg_color['brown']	= '0;33'; $fg_color['yellow']	= '1;33';
	$fg_color['lgray']	= '0;37'; $fg_color['white']	= '1;37';

	// Background Colors
	$bg_color['black']	= '40'; $bg_color['red']	= '41';
	$bg_color['green']	= '42'; $bg_color['yellow']	= '43';
	$bg_color['blue']	= '44'; $bg_color['magenta']	= '45';
	$bg_color['cyan']	= '46'; $bg_color['lgray']	= '47';

	$colored_string = "";

	$fg = isset($fg_color[$fgc]) ? "\033[" . $fg_color[$fgc] . "m" : "";
	$bg = isset($bg_color[$bgc]) ? "\033[" . $bg_color[$bgc] . "m" : "";
	#$tail = ($fg=="" && $bg=="") ? "" : "\033[0m";
	$tail = "\033[0m";

	return $fg . $bg . $string . $tail;
}

function mysql_show_config(){
	echo colorStr("---------------------------------------------\n","cyan");
	echo colorStr("Configuracion de Usuario para Backups - MySQL\n","cyan");
	echo colorStr("---------------------------------------------\n","cyan");
	echo "\n";
	echo colorStr("Paso 1: db_connectse al servidor mysql donde\n".
		      "        se guardan las bd que desea resguardar\n","yellow");
	echo "\n";
	echo colorStr("Paso 2: Ejecutar el siguiente query sql:\n","yellow");
	echo colorStr("Nota  : Reemplaze el ip y la clave con sus datos\n","yellow");
	echo "\n";
	echo "GRANT SELECT, SHOW DATABASES, LOCK TABLES, " .
		"SHOW VIEW\n ON *.* to 'backup'@'this-ip' " .
		"identified by 'mi-clave-bd';\n";
	echo "FLUSH PRIVILEGES;\n\n";
}

function show_help(){

	echo colorStr("magnet-bk ". VERSION ." - Facilitador " .
			 "de Backups Remoto BD - " .
			 "By >>bluenix\n","cyan");
	echo colorStr("Uso: magnet-bk.php [-nv][-y][-nz]\n","cyan");
	echo colorStr("Opc: -nv  : No verbose\n","white");
	echo colorStr("Opc: -y   : Yes to All\n","white");
	echo colorStr("Opc: -nz  : Without Gzip\n","white");
	echo colorStr("Opc: --config-mysql  : MySQL User Config Steps\n\n",
			"white");
}
 
class mysql_cli
{
	private $m_ad;	// Database Instance
	
	function __construct($strServer,$strBase,
				$strPuerto,$strUser,$strPass)
	{
		$this->m_ad = new mysql_dac($strServer,$strBase,
				$strPuerto,$strUser,$strPass);
	}

	function __destruct(){
		unset($this->m_ad);
	}

	public function get_databases()
	{
		$strSQL = "SHOW DATABASES";
                $result = $this->m_ad->sql_query($strSQL,false);
                $result = $this->m_ad->get_recordset($result);
                if (!$result)
                {
			throw new exception("No se pudieron recuperar ".
					"las bases de datos", 5504);
                        return false;
                }
                return $result;
	}

}

class mysql_dac
{
	private $m_IDCnx;		// Connection ID
	private $m_Res;			// Query Result Resource
	private $m_EnTrx = false;	// This is a Transaction ?
	private $m_rec = array();	// Record
	private $m_rst = array();	// Recordset
	
	/* Constructor y Destructor */
	function __construct($strServer,$strBase,$intPort,
				$strUser,$strPass)
	{
		$this->m_EnTrx = false;
		$this->db_connect($strServer,$strBase,$intPort,
				$strUser,$strPass);
	}
	
	function __destruct()
	{
		$result = false;
		
		// If there are an active connection
		if ($this->m_IDCnx)
		{
			// End any transaction
			if ($this->m_EnTrx == true)
			{
				@mysqli_commit($this->m_IDCnx);
				$this->m_EnTrx = false;
			}
			
			// Free resources
			if ($this->m_Res)
			{
				@mysqli_free_result($this->m_Res);
			}
			
			// And close the connection
			$result = @mysqli_close($this->m_IDCnx);
		}
		else
		{
			// If there are no connection then false
			$result = false;
		}
		
		// Clean members variables
		unset($this->m_EnTrx);
		unset($this->m_IDCnx);
		unset($this->m_rec);
		unset($this->m_rst);
		unset($this->m_Res);
		
		return $result;
	}
	
	/**
	 * db_connect
	 * 
	 * Make a mysql database connection
	 * @author Fernando Daz Sanchez <sirfids@gmail.com>
	 * @param $strTipoUsuario
	 * @return Connection ID or false
	 * */
	private function db_connect($strServer,$strBase,$intPort,
		$strUser,$strPass)
	{
		$this->m_IDCnx = @mysqli_connect($strServer,
							$strUser,
							$strPass,
							$strBase,
							$intPort);
		
		// If some error happens
		if( mysqli_connect_errno() )
		{
			// Make an exception
			throw new exception(mysqli_connect_error(), 
					    mysqli_connect_errno());
    		}
    	
    	return ( $this->m_IDCnx ) ? $this->m_IDCnx : false;
	}
	
	/**
	 * sql_query
	 * 
	 * Make a mysql query or execute an store procedure
	 * @author Fernando Diaz Sanchez <sirfids@gmail.com>
	 *
	 * @param $strSQL
	 * @param $inTransaction
	 * @return unknown
	 * @todo LIMIT
	 */
	public function sql_query($strSQL, $inTransaction = false)
	{
		// Free previous result
		if ($this->m_Res) 
			@mysqli_free_result($this->m_Res);
		
		// Delete previous query result
		unset($this->m_Res);
		
		// Check if query is not empty
		if ($strSQL != '')
		{
			// Check transaction starting
			if ( $inTransaction == true && 
				!$this->m_EnTrx )
			{
				$this->m_EnTrx = true;
				
				if ( !@mysqli_autocommit($this->m_IDCnx,
							false) )
				{
					throw new Exception( 
						mysqli_error($this->m_IDCnx),
						mysqli_errno($this->m_IDCnx) 
					);
				}
			}
			
			// Execute SQL Query
			$this->m_Res = @mysqli_query($this->m_IDCnx,$strSQL);
			@mysqli_next_result($this->m_IDCnx);
			
			// Check if everything is ok
			if ( $this->m_Res )
			{
				// Check end of transaction
				if ($inTransaction == false && 
					$this->m_EnTrx)
				{
					$this->m_EnTrx = false;
					
					if ( !@mysqli_commit($this->m_IDCnx) )
					{
						@mysqli_rollback($this->m_IDCnx);
						@mysqli_autocommit($this->m_IDCnx,
									true);
						throw new 
						Exception( 
							mysqli_error($this->m_IDCnx),
							mysqli_errno($this->m_IDCnx) 
							);
					}
				}
				
				return $this->m_Res;
			}
			else
			{
				// If query fails, check if there are
				// no transaction
				if ( $this->m_EnTrx )
				{
					@mysqli_rollback($this->m_IDCnx);
					@mysqli_autocommit($this->m_IDCnx,true);
					throw new 
					Exception( 
						mysqli_error($this->m_IDCnx),
						mysqli_errno($this->m_IDCnx) 
						);
				}
				
				$this->m_EnTrx = false;
				throw new 
				Exception( 
					mysqli_error($this->m_IDCnx),
					mysqli_errno($this->m_IDCnx) 
					);
			}

		}
		else
		{
			// If SQL Query is empty
			// check and close the transaction
			if ($inTransaction == false && 
				$this->m_EnTrx == true)
			{
				$this->m_EnTrx = false;
				
				// If COMMIT fails
				if ( !@mysqli_commit($this->m_IDCnx) )
				{
					// ROLLBACK comes
					@mysqli_rollback($this->m_IDCnx);
					@mysqli_autocommit($this->m_IDCnx,true);
					throw new 
					Exception( 
						mysqli_error($this->m_IDCnx),
						mysqli_errno($this->m_IDCnx) 
						);
				}
			}
			
			$this->m_Res = false;
			return false;
		}
	}
	
	/**
	 * get_recordset
	 * 
	 * Get a recordset from an SQL Query
	 * @author Fernando Diaz Sanchez <sirfids@gmail.com>
	 *
	 * @param $resQueryId
	 * @param $bolAssoc
	 * @return recordset o false
	 */
	public function get_recordset($resQueryId = 0, 
		$bolAssoc = true)
	{
		// If there are no Query result
		if( !$resQueryId )
		{
			$resQueryId = $this->m_Res;
		}
		
		// If Query ID is OK
		if ( $resQueryId )
		{
			$result = false;
			
			// Set the result type
			$type = ( $bolAssoc ) ? MYSQLI_ASSOC : MYSQLI_BOTH;
			
			do
			{
				$this->m_rst = @mysqli_fetch_array($resQueryId, 
								   $type);
				if ( $this->m_rst ) $result[] = $this->m_rst;	
			}
			while( $this->m_rst );
			
			return $result;
		}
		
		return false;
	}

}

?>