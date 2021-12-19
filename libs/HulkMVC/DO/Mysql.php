<?php
/* $Id: Mysql.php 714 2007-12-17 03:46:28Z chalmers $ */
/**
 * Mysql.php
 * 
 * <p>Provides concrete implementation of the HulkMVC Data Object using the native 
 * MySQL functions as the mechanism.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Data Object
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 714 $
 */

// Bring in the class interface
require_once 'HulkMVC/DO.php';

/**
 * HulkMVC_DO_Mysql
 *  
 * <p>A concrete implementation of the HulkMVC Data Objects in which the native 
 * MySQL functions are wrapped in a class hierarchy which is a copy of the PDO
 * interface.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Data Object
 * @access public
 */
class HulkMVC_DO_Mysql implements HulkMVC_DO {
    
    private $db;
    
    /**
     * Creates the HulkMVC Data Object using native MySQL functions as the 
     * underlying mechanism.
     *
     * @param string $dsn The database connection string.
     * @param string $username The database username to connecto with.
     * @param string $password The username's passwaord to connecto with.
     * @param array $options Driver options used to setup the database 
     * connection.  Optional.
     * @throws HulkMVC_Exception_DO If error in connection to database.
     */
    public function __construct($dsn, $username, $password, $options = array()) {
        
        $parts = explode(':', $dsn);
        $locationParts = explode(';', $parts[1]);
        
        $firstParts = explode('=', $locationParts[0]);
        $secondParts = explode('=', $locationParts[1]);
        
        if ($firstParts[0] === 'host') {
            $host = $firstParts[1];
            $schema = $secondParts[1];
        }
        else if ($firstParts[0] === 'schema') {
            $schema = $firstParts[1];
            $host = $secondParts[1];
        }
        else {
            throw new HulkMVC_Exception_DO("DSN format incorrectly specified {$dsn}");
        }
        
        // Connecto to database server
        $this->db = mysql_connect($host, $username, $password);
        if (!$this->db) {
            throw new HulkMVC_Exception_DO("Unable to connect the database server specified by {$dsn}");
        }
        
        // Select the schema
        if (!mysql_select_db($schema, $this->db)) {
            throw new HulkMVC_Exception_DO("Unable to select the database {$schema}");
        }
    }
    
    /**
     * @see HulkMVC_DO::getAttribute()
     */
    public function getAttribute($attribute) {
        
        switch ($attribute) {
            
            case HulkMVC_DO::ATTR_DRIVER_NAME :
                return 'mysql';
                
            case HulkMVC_DO::ATTR_AUTOCOMMIT :
            case HulkMVC_DO::ATTR_CASE :
            case HulkMVC_DO::ATTR_CLIENT_VERSION :
            case HulkMVC_DO::ATTR_CONNECTION_STATUS :
            case HulkMVC_DO::ATTR_DRIVER_NAME :
            case HulkMVC_DO::ATTR_ERRMODE :
            case HulkMVC_DO::ATTR_ORACLE_NULLS :
            case HulkMVC_DO::ATTR_PERSISTENT :
            case HulkMVC_DO::ATTR_PREFETCH :
            case HulkMVC_DO::ATTR_SERVER_INFO :
            case HulkMVC_DO::ATTR_SERVER_VERSION :
            case HulkMVC_DO::ATTR_TIMEOUT :
                return false;
        }
    }
    
    /**
     * @see HulkMVC_DO::setAttribute()
     */
    public function setAttribute($attribute, $value) {
    }
    
    /**
     * @see HulkMVC_DO::errorCode()
     */
    public function errorCode() {
    }
    
    /**
     * @see HulkMVC_DO::errorInfo()
     */
    public function errorInfo() {
    }
    
    /**
     * @see HulkMVC_DO::beginTransaction()
     */
    public function beginTransaction() {
    }
    
    /**
     * @see HulkMVC_DO::commit()
     */
    public function commit() {
    }
    
    /**
     * @see HulkMVC_DO::rollback()
     */
    public function rollback() {
    }
    
    /**
     * @see HulkMVC_DO::exec()
     */
    public function exec($statement) {
    }
    
    /**
     * @see HulkMVC_DO::query()
     */
    public function query($statement) {
    }
    
    /**
     * @see HulkMVC_DO::prepare()
     */
    public function prepare($statement, $options) {
    }
    
    /**
     * @see HulkMVC_DO::lasInsertId()
     */
    public function lastInsertId($name) {
    }
    
    /**
     * @see HulkMVC_DO::quote()
     */
    public function quote($string, $type) {
    }
}

?>