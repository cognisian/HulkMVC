<?php
/* $Id: DO.php 714 2007-12-17 03:46:28Z chalmers $ */
/**
 * DO.php
 * 
 * <p>Provides an interface to the multiple different access methods to a database</p>
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

/**
 * HulkMVC_DO
 *  
 * <p>A wrapper object to the PDO and native database access methods.  This wrapper 
 * directly mimics the PDO objects.  This DataObject object is responsible for the 
 * connection to a database.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Data Object
 * @access public
 */
interface HulkMVC_DO {
    
    // Prepared statement properties
    const PARAM_NULL = 0; 
    const PARAM_INT = 1;
    const PARAM_STR = 2;
    const PARAM_LOB = 3;
    const PARAM_STMT = 4;
    const PARAM_BOOL  = 5; 
    const PARAM_INPUT_OUTPUT = 2147483648; 
    
    // Cursor properties
    const FETCH_USE_DEFAULT = 0;
    const FETCH_LAZY = 1; 
    const FETCH_ASSOC = 2;  
    const FETCH_NUM = 3; 
    const FETCH_BOTH = 4; 
    const FETCH_OBJ = 5; 
    const FETCH_BOUND = 6; 
    const FETCH_COLUMN = 7; 
    const FETCH_CLASS = 8; 
    const FETCH_INTO = 9; 
    const FETCH_FUNC =10;
    const FETCH_NAMED = 11;
    const FETCH_KEY_PAIR = 12;
    
    const FETCH_GROUP = 65536;    
    const FETCH_UNIQUE = 196608;
    const FETCH_CLASSTYPE = 262144;
    const FETCH_SERIALIZE = 524288; 
    const FETCH_PROPS_LATE = 1048576;
    
    // Get and set attrributes of the driver
    const ATTR_AUTOCOMMIT = 1; 
    const ATTR_PREFETCH = 1; 
    const ATTR_TIMEOUT = 2; 
    const ATTR_ERRMODE = 3; 
    const ATTR_SERVER_VERSION = 4; 
    const ATTR_CLIENT_VERSION = 5; 
    const ATTR_SERVER_INFO = 6; 
    const ATTR_CONNECTION_STATUS = 7;
    const ATTR_CASE = 8; 
    const ATTR_CURSOR_NAME = 9; 
    const ATTR_CURSOR = 10; 
    const ATTR_ORACLE_NULLS  = 11; 
    const ATTR_PERSISTENT = 12; 
    const ATTR_STATEMENT_CLASS = 13;
    const ATTR_FETCH_TABLE_NAMES = 14;
    const ATTR_FETCH_CATALOG_NAMES = 15;
    const ATTR_DRIVER_NAME = 16;  
    const ATTR_STRINGIFY_FETCHES = 17;
    const ATTR_MAX_COLUMN_LEN = 18;
    const ATTR_DEFAULT_FETCH_MODE = 19;
    const ATTR_EMULATE_PREPARES = 20;
    const ATTR_DRIVER_SPECIFIC = 1000;
    
    // Error modes
    const ERRMODE_SILENT = 0; 
    const ERRMODE_WARNING = 1; 
    const ERRMODE_EXCEPTION = 2;

    // Translation
    const CASE_NATURAL = 0; 
    const CASE_LOWER = 1; 
    const CASE_UPPER = 2; 
    
    // Oracle interop
    const NULL_NATURAL = 0;
    const NULL_EMPTY_STRING = 1;
    const NULL_TO_STRING = 2;
    
    // Cursors properties
    const FETCH_ORI_NEXT = 0; 
    const FETCH_ORI_PRIOR = 1; 
    const FETCH_ORI_FIRST = 2; 
    const FETCH_ORI_LAST = 3; 
    const FETCH_ORI_ABS = 4; 
    const FETCH_ORI_REL = 5;
    
    const CURSOR_FWDONLY = 0; 
    const CURSOR_SCROLL = 1; 
    
    // Result event properties
    const ERR_NONE = '00000'; 
    const PARAM_EVT_ALLOC = 0;
    const PARAM_EVT_FREE = 1;
    const PARAM_EVT_EXEC_PRE = 2; 
    const PARAM_EVT_EXEC_POST = 3; 
    const PARAM_EVT_FETCH_PRE = 4; 
    const PARAM_EVT_FETCH_POST = 5; 
    const PARAM_EVT_NORMALIZE = 6;

    /**
     * Sets an attribute on the database handle. Some of the available generic 
     * attributes are listed below; some drivers may make use of additional driver specific attributes.
     * 
     * <ul>
     *      <p>ATTR_CASE: Force column names to a specific case.</p>
     *      <li>CASE_LOWER: Force column names to lower case.</li>
     *      <li>CASE_NATURAL: Leave column names as returned by the database driver.</li>
     *      <li>CASE_UPPER: Force column names to upper case.</li>
     * </ul>
     * <ul>
     *      <p>ATTR_ERRMODE: Error reporting.</p>
     *      <li>ERRMODE_SILENT: Just set error codes.</li>
     *      <li>ERRMODE_WARNING: Raise E_WARNING.</li>
     *      <li>ERRMODE_EXCEPTION: Throw exceptions.</li>
     * </ul>
     *      <p>ATTR_ORACLE_NULLS (available with all drivers, not just 
     *              Oracle)Conversion of NULL and empty strings.</p>
     *      <li>NULL_NATURAL: No conversion.</li>
     *      <li>NULL_EMPTY_STRING: Empty string is converted to NULL.</li>
     *      <li>NULL_TO_STRING: NULL is converted to an empty string.</li>
     * </ul>
     * <p>ATTR_STRINGIFY_FETCHES: Convert numeric values to strings when 
     *          fetching. Requires bool.</p>
     * <p>ATTR_STATEMENT_CLASS: Set user-supplied statement class derived from 
     *          {@link HulkMVC_DO_Statement}. Cannot be used with persistent 
     *          instances. 
     *          Requires array(string classname, array(mixed constructor_args)).</p>
     * <p>ATTR_AUTOCOMMIT (available in OCI, Firebird and MySQL): Whether 
     *          to autocommit every single statement.</p>
     * <p>MYSQL_ATTR_USE_BUFFERED_QUERY (available in MySQL): Use 
     *          buffered queries.</p>
     *
     * @param int $attribute
     */
    public function setAttribute($attribute, $value);
    
    /**
     * Gets the value of the attribute of the driver class.
     * 
     * <p>
     * One of the HulkMVC_DO::ATTR_* constants. The constants that apply to 
     * database connections are as follows:
     * <ul>
     *      <li>ATTR_AUTOCOMMIT</li>
     *      <li>ATTR_CASE</li>
     *      <li>ATTR_CLIENT_VERSION</li>
     *      <li>ATTR_CONNECTION_STATUS</li>
     *      <li>ATTR_DRIVER_NAME</li>
     *      <li>ATTR_ERRMODE</li>
     *      <li>ATTR_ORACLE_NULLS</li>
     *      <li>ATTR_PERSISTENT</li>
     *      <li>ATTR_PREFETCH</li>
     *      <li>ATTR_SERVER_INFO</li>
     *      <li>ATTR_SERVER_VERSION</li>
     *      <li>ATTR_TIMEOUT</li>
     * </ul> 
     * </p>
     *
     * @param int $attribute The driver attribute to retrieve
     * @return mixed The value of the requested attribute.
     */
    public function getAttribute($attribute);
    
    public function errorCode();
    public function errorInfo();
    
    public function beginTransaction();
    public function commit();
    public function rollback();
    
    public function exec($statement);
    public function query($statement);
    public function prepare($statement, $driver_options);
    public function lastInsertId($name);
    public function quote($string, $parameter_type);
    
}
?>