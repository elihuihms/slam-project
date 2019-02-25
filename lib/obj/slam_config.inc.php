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
		if (!array_key_exists('config_version', $this->values))
			$this->values['config_version'] = '1.0';

		if (trim($this->values['version']) != trim($this->values['config_version'])) {
			$this->update_config();
			$this->values = array_merge($this->values,$this->parse_config());
			$this->errors[] = "Note: updated config file to version ".$this->values['version'].".";
		}
		
		/* check for some absolutely required values in the config file */
		if(!is_dir($this->values['path']))
			exit("The installation path specified in your configuration file (\"{$this->values['path']}\") is not valid. Please check your \"configuration.ini\" file or contact your system administrator.");

		if(!is_dir($this->values['file manager']['archive_dir']) || !is_writable($this->values['file manager']['archive_dir']))
			exit("The file archive path specified in your configuration file (\"{$this->values['file manager']['archive_dir']}\") does not exist or is not writeable. Please check your \"configuration.ini\" file or contact your system administrator.");

		if(!is_dir($this->values['file manager']['temp_dir']) || !is_writable($this->values['file manager']['temp_dir']))
			exit("The file archive path specified in your configuration file (\"{$this->values['file manager']['temp_dir']}\") does not exist or is not writeable. Please check your \"configuration.ini\" file or contact your system administrator.");
		
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

	private function update_ini_file(&$lines, $new_line, $after_key=false, $replace=false)
	{
		/*
			Provided an array (of lines) from an ini-formatted file, insert $new_line after the specified key. If replace==true, replace the line completely.
			Returns true if the addition or replacement has been performed (i.e. if the $after_key was found), otherwise false.
		*/

		for ($i=0; $i<count($lines); $i++) {
			$line = explode("=", $lines[$i]);
			if ( $i == 0 && $after_key === false) {
				array_unshift( $lines, $new_line );
				return true;
			} else if ( rtrim($line[0]) == $after_key ) {
				if ($replace) {
					$lines[$i] = $new_line;
					return true;
				}
				else {
					array_splice( $lines, $i+1, 0, $new_line );
					return true;
				}
			}
		}

		return false;
	}

	private function update_config()
	{
		/*
			updates an old config file to conform with the new style
		*/

		if(!is_writable('configuration.ini'))
			exit("Fatal error: SLAM configuration.ini version is too old and is not writeable for updating. Please contact your system administrator.");

		if (($old_config_arr = @file('configuration.ini', FILE_IGNORE_NEW_LINES)) === false)
			exit("Fatal error: Could not read configuration file during update of old configuration.ini. Please contact your system administrator.");

		if (($def_config_ini = @parse_ini_file('install'.DIRECTORY_SEPARATOR.'defaults.ini',true)) === false)
			exit("Fatal error: Could not read configuration defaults during update of old configuration.ini. Please contact your system administrator.");

		update_auto_defaults($def_config_ini); /* replace any 'auto' values */

		/*
			1.0 -> 1.2 patching
		*/
		$dirty = false;
		if (!array_key_exists('db_port', $this->values)) {
			$this->update_ini_file( $old_config_arr, 'db_port = "'.$def_config_ini['SLAM_DB_PORT'].'"', 'db_server');
			$dirty = true;
		}
		if (!array_key_exists('db_charset', $this->values)) {
			$this->update_ini_file( $old_config_arr, 'db_charset = "'.$def_config_ini['SLAM_DB_CHARSET'].'"', 'db_name');
			$dirty = true;
		}
		if ($dirty) {
			if (!$this->update_ini_file( $old_config_arr, 'config_version = "'.$this->values['version'].'"', 'config_version', true))
				$this->update_ini_file( $old_config_arr, 'config_version = "'.$this->values['version'].'"');
			$this->update_ini_file( $old_config_arr, '; Updated to version '.$this->values['version'].' config style on '.date('Y-m-d H:i:s'));
			
			copy( 'configuration.ini', 'configuration-'.time().'.ini');
			if (file_put_contents('configuration.ini', implode(PHP_EOL, $old_config_arr)) === false)
				exit("Fatal error: Could not write updates to configuration.ini. Please contact your system administrator.");
		}
	}
}

?>