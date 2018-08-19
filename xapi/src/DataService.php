<?php

namespace XapiProxy;

use \XapiProxy\ilInitialisation as ilInitialisation;
use \XapiProxy\ilUtil as ilUtil;

class DataService
{
    public static function initIlias($client_id, $client_token) {
        define ("CLIENT_ID", $client_id);
        define('IL_COOKIE_HTTPONLY', true); // Default Value
		define('IL_COOKIE_EXPIRE', 0);
		define('IL_COOKIE_PATH', '/');
		define('IL_COOKIE_DOMAIN', '');
        require_once "./Services/Utilities/classes/class.ilUtil.php";
        ilInitialisation::initIliasIniFile();
        ilInitialisation::initClientIniFile();
        ilInitialisation::initDatabase();
        //ilInitialisation::initLog();
    }
}

/**
 *  Class: ilInitialisation_Public
 *  Helper class that derives from ilInitialisation in order
 *  to 'publish' some of its methods that are (currently)
 *  required by XapiProxy and included plugin classes
 *
 */
require_once('Services/Init/classes/class.ilInitialisation.php');
class ilInitialisation extends \ilInitialisation {
    /**
    * Function; initGlobal($a_name, $a_class, $a_source_file)
    *  Derive from protected to public...
    *
    * @see \ilInitialisation::initGlobal($a_name, $a_class, $a_source_file)
    */
    public static function initGlobal($a_name, $a_class, $a_source_file = null) {
        return parent::initGlobal($a_name, $a_class, $a_source_file);
    }

    /**
    * Function: initDatabase()
    *  Derive from protected to public...
    *
    * @see \ilInitialisation::initDatabase()
    */
    public static function initDatabase() {
        if (!isset($GLOBALS['ilDB'])) {
            parent::initGlobal("ilBench", "ilBenchmark", "./Services/Utilities/classes/class.ilBenchmark.php");
            parent::initDatabase();
        }
    }

    /**
    * Function: initIliasIniFile()
    *  Derive from protected to public...
    *
    * @see \ilInitialisation::initIliasIniFile()
    */
    public static function initIliasIniFile() {
        if (!isset($GLOBALS['ilIliasIniFile'])) {
            parent::initIliasIniFile();
        }
    }
    
    /**
    * Function: initClientIniFile()
    *  Derive from protected to public...
    *
    * @see \ilInitialisation::initIliasIniFile()
    */
    public static function initClientIniFile() {
        if (!isset($GLOBALS['initClientIniFile'])) {
            parent::initClientIniFile();
        }
    }
    
}
