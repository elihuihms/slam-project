<?php

require('install/lib/actions.inc.php'); /* necessary for updating old config files via update_auto_defaults() */

class SLAMconfig
{
	public $errors;
	public $values;
	public $db;
	public $html;
	
	public $categories;
	public $projects;
	
	function __construct()
	{
		$this->errors = array();
		
		$this->values['version'] = '1.2';
		$this->values['build'] = '20190127';
		
		// do some basic initializing
		if( array_key_exists('HTTPS',$_SERVER) )
			$http = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
		else
			$http = 'http://';
		$this->html['url'] = $http.dirname($_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']).'/';
		$this->html['headers'] = array();
		$this->html['onload'] = array();
		$this->html['abort'] = '';
			
		$this->values = array_merge($this->values,$this->parse_config());
		$this->values = array_merge($this->values,$this->parse_prefs());

		/* check to see if we're using an old config file version */
		if(!array_key_exists('slam_version', $this->values))
			$this->values['slam_version'] = '1.0';
		if($this->values['version'] != $this->values['slam_version']) {
			$this->update_config();
			$this->values = array_merge($this->values,$this->parse_config());
		}
		
		/* check for some absolutely required values in the config file */
		
		if(!is_dir($this->values['path']))
			exit("The installation path specified in your configuration file (\"{$this->values['path']}\") is not valid. Please check your \"configuration.ini\" file or contact your system administrator.");
		
		if(empty($this->values['category_table']))
			exit("The \"category_table\" option in the \"configuration.ini\" file is missing. Please check your configuration file or contact your system administrator.");

		if(empty($this->values['user_table']))
			exit("The \"category_table\" option in the \"configuration.ini\" file is missing. Please check your configuration file or contact your system administrator.");			
				
		return;
	}

	private function parse_config()
	{
		/*
			reads the SLAM configuration file and returns the configuration associative array
		*/
				
		if (($r = @parse_ini_file('configuration.ini',true)) === false)
			die('Fatal error: Could not read your "configuration.ini" file. Please <a href="install/index.php">install SLAM</a> or contact your system administrator.');
		
		return $r;
	}
	
	private function parse_prefs()
	{
		/*
			reads the SLAM preferences file and returns the configuration associative array
		*/
		
		if (($r = @parse_ini_file('preferences.ini',true)) === false)
			die('Fatal error: Could not read your "preferences.ini" file. Please <a href="install/index.php">install SLAM</a> or contact your system administrator.');
			
		return $r;
	}

	private function update_ini_file($lines, $after_key, $new_line, $replace=False) {
		/*
			Provided an array (of lines) from an ini-type file, insert $new_line after the specified key. If replace==true, replace the line completely.
		*/

		for ($i=0; $i<count($old_config_arr); $i++) {
			$line = explode("=", $lines[$i]);
			if ( rtrim($line[0]) == $after_key ) {
				if ($replace) {
					$lines[$i] = $new_line;
				} else {
					$lines = array_splice( $lines, $i+1, 0, array($new_line) );
				}
			}
		}

		return $lines;
	}

	private function update_config() {
		/*
			updates an old config file to conform with the new style
		*/

		if(!is_writable('configuration.ini'))
			exit("Fatal error: SLAM configuration.ini version is too old and is not writeable for updating. Please contact your system administrator.");

		if (($old_config_arr = @file('configuration.ini', FILE_IGNORE_NEW_LINES)) === false)
			exit("Fatal error: Could not read configuration file during update of old configuration.ini. Please contact your system administrator.");

		if (($def_config_ini = @parse_ini_file('install'.DIRECTORY_SEPARATOR.'defaults.ini',true)) === false)
			exit("Fatal error: Could not read configuration defaults during update of old configuration.ini. Please contact your system administrator.");

		$def_config_ini = update_auto_defaults($def_config_init); /* replace any 'auto' values */

		/*
			1.0 -> 1.2 patching
		*/
		$dirty = false;
		if (!array_key_exists('db_port', $this->values) {
			$old_config_arr = update_ini_file( $old_config_arr, 'db_server', "db_port=\"".$def_config_ini['db_port']."\"");
			$dirty = true;
		}
		if (!array_key_exists('db_charset', $this->values) {
			$old_config_arr = update_ini_file( $old_config_arr, 'db_charset', "db_charset=\"".$def_config_ini['db_charset']."\"");
			$dirty = true;
		}
		if ($dirty){
			$old_config_arr = update_ini_file( $old_config_arr, 'slam_version', "; Updated to version ".$this->values['version']." config style on ".date("Y-m-d H:i:s"));
			$old_config_arr = update_ini_file( $old_config_arr, 'slam_version', "slam_version=\"".$this->values['version']."\"", true);
			
			if (file_put_contents('configuration.ini', implode(PHP_EOL, $old_config_arr)) !== true)
				exit("Fatal error: Could not write updates to configuration.ini. Please contact your system administrator.");
		}
	}
}

?>