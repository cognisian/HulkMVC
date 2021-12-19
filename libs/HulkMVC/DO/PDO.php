<?php
/* $Id: PDO.php 714 2007-12-17 03:46:28Z chalmers $ */
/**
 * PDO.php
 * 
 * <p>Provides concrete implementation of the HulkMVC Data Object using the PDO mechanism.</p>
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
 * HulkMVC_DO_PDO
 *  
 * <p>A concrete implementation of the HulkMVC Data Objects utilizing the PDO 
 * mechanism.  The class is a proxy to the PDO objects and uses the __call magic
 * function as a wrapper function to all functions of the PDO object.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Data Object
 * @access public
 */
class HulkMVC_DO_PDO implements HulkMVC_DO {
    
    private $pdo;
    
    /**
     * Creates the HulkMVC Data Object using PDO as the underlying mechanism.
     *
     * @param string $dsn The database connection string.
     * @param string $username The database username to connecto with.
     * @param string $password The username's passwaord to connecto with.
     * @param array $options Driver options used to setup the database 
     * connection.  Optional.
     * @throws HulkMVC_Exception_DO If error in connection to database.
     */
    public function __construct($dsn, $username, $password, $options = array()) {
         
        try {
            // Create underlying PDO object and associate the HulkMVC statement object with
            // any result sets
            $this->pdo = new PDO($dsn, $username, $password, $options);
            $this->pdo->setAttribute(HulkMVC_DO::ATTR_STATEMENT_CLASS, 
                    array('HulkMVC_DO_PDOStmt', array($this->pdo)));
        }
        catch (PDOException $ex) {
            throw new HulkMVC_Exception_DO($ex->getMessage());
        }
    }
    
    /**
     * @see HulkMVC_DO::getAttribute()
     */
    public function getAttribute($attribute) {
        return $this->pdo->getAttribute($attribute);
    }
    
    /**
     * @see HulkMVC_DO::setAttribute()
     */
    public function setAttribute($attribute, $value) {
        $this->pdo->setAttribute($attribute, $value);
    }
    
    /**
     * @see HulkMVC_DO::errorCode()
     */
    public function errorCode() {
        return $this->pdo->errorCode();
    }
    
    /**
     * @see HulkMVC_DO::errorInfo()
     */
    public function errorInfo() {
        return $this->pdo->errorInfo();
    }
    
    /**
     * @see HulkMVC_DO::beginTransaction()
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * @see HulkMVC_DO::commit()
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * @see HulkMVC_DO::rollback()
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * @see HulkMVC_DO::exec()
     */
    public function exec($statement) {
        return $this->pdo->exec($statement);
    }
    
    /**
     * @see HulkMVC_DO::query()
     */
    public function query($statement) {
        return $this->pdo->query($statement);
    }
    
    /**
     * @see HulkMVC_DO::prepare()
     */
    public function prepare($statement, $options) {
        return $this->pdo->prepare($statement, $options);
    }
    
    /**
     * @see HulkMVC_DO::lastInsertId()
     */
    public function lastInsertId($name) {
        return $this->pdo->lastInsertId($name);
    }
    
    /**
     * @see HulkMVC_DO::quote()
     */
    public function quote($string, $type) {
        return $this->pdo->query($string, $type);
    }
}

?>