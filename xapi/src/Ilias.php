<?php

namespace XapiProxy;

use XapiProxy\ilInitialisation as ilInitialisation;

class Ilias
{
    
    /**
    * Read and return the default client-ID from the ilias.ini file
    * @return string
    */
    protected static function getIniDefaultClientId() {
        require_once('./Services/Init/classes/class.ilIniFile.php');
        $ini = new \ilIniFile('./ilias.ini.php');
        $ini->read();
        return $ini->readVariable("clients", "default");
    }

    public static function initIlias($cient = null) {
        define ("CLIENT_ID", self::getIniDefaultClientId());
        ilInitialisation::initIliasIniFile();
        ilInitialisation::initClientIniFile();
        ilInitialisation::initDatabase();
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
