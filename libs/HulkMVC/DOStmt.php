<?php
/* $Id: DOStmt.php 714 2007-12-17 03:46:28Z chalmers $ */
/**
 * DOStmt.php
 * 
 * <p>Provides an interface to the multiple different access methods to a 
 * database result set.</p>
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
 * HulkMVC_DOStmt
 *  
 * <p>A wrapper object to the PDO and native database result set access methods.
 * This wrapper directly mimics the PDOStatement object.  This DataObject object is 
 * responsible for the query result set.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Data Object
 * @access public
 */
interface HulkMVC_DOStmt extends IteratorAggregate {

    /**
     * Arranges to have a particular variable bound to a given column in the result
     * set from a query. Each call to {@link HulkMVC_DOStmt::fetch()} or 
     * {@link HulkMVC_DOStmt::fetchAll()} will update all the variables that are 
     * bound to columns.
     *
     * @param mixed $column The index or name of the column in the result set. 
     * @param mixed $param The PHP variable to bind to.
     * @param int $type Data type of the parameter, specified by the PDO::PARAM_* 
     * constants.
     * @return bool Returns TRUE on success or FALSE on failure.
     * @see HulkMVC_DOStmt::execute()
     * @see HulkMVC_DOStmt::fetch()
     * @see HulkMVC_DOStmt::fetchAll()
     * @see HulkMVC_DOStmt::fetchColumn()
     */
    public function bindColumn($column, &$param, $type);
    
    /**
     * Binds a PHP variable to a corresponding named or question mark placeholder 
     * in the SQL statement that was use to prepare the statement. 
     * 
     * <p>Unlike {@link HulkMVC_DOStmt::bindValue()}, the variable is bound as a 
     * reference and will only be evaluated at the time that 
     * {@link HulkMVC_DOStmt::execute()} is called.</p>
     * 
     * <p>Most parameters are input parameters, that is, parameters that are used 
     * in a read-only fashion to build up the query. Some drivers support the 
     * invocation of stored procedures that return data as output parameters, and 
     * some also as input/output parameters that both send in data and are updated 
     * to receive it.</p> 
     *
     * @param mixed $parameter Parameter identifier. For a prepared statement using 
     * named placeholders, this will be a parameter name of the form :name. For 
     * a prepared statement using question mark placeholders, this will be the 1
     * -indexed position of the parameter.
     * @param mixed $variable Name of the PHP variable to bind to the SQL statement 
     * parameter.
     * @param int $data_type Explicit data type for the parameter using the 
     * HulkMVC_DO::PARAM_* constants. Defaults to PHP native type. To return an 
     * INOUT parameter from a stored procedure, use the bitwise OR operator to set 
     * the HulkMVC_DO::PARAM_INPUT_OUTPUT bits for the data_type  parameter.
     * Optional.
     * @param int $length Length of the data type. To indicate that a parameter is 
     * an OUT parameter from a stored procedure, you must explicitly set the length.
     * Optional.
     * @param mixed $options The database options.  Optional.
     * @return bool Returns TRUE on success or FALSE on failure.
     * @see HulkMVC_DO::prepare()
     * @see HulkMVC_DOStmt::execute()
     * @see HulkMVC_DOStmt::bindValue()
     */
    //public function bindParam($parameter, $variable, $data_type, $length);
    
    /**
     * Binds a value to a corresponding named or question mark placeholder in the 
     * SQL statement that was use to prepare the statement.
     *
     * @param mixed $parameter Parameter identifier. For a prepared statement using 
     * named placeholders, this will be a parameter name of the form :name. For a 
     * prepared statement using question mark placeholders, this will be the 
     * 1-indexed position of the parameter.
     * @param mixed $value The value to bind to the parameter.
     * @param mixed $data_type Explicit data type for the parameter using the 
     * HulkMVC_DO::PARAM_* constants. Defaults to PHP native type.
     * @return bool Returns TRUE on success or FALSE on failure.
     * @see HulkMVC_DO::prepare()
     * @see HulkMVC_DOStmt::execute()
     * @see HulkMVC_DOStmt::bindParam()
     */
    public function bindValue($parameter, $value, $data_type);
    
    /**
     * Frees up the connection to the server so that other SQL statements may be 
     * issued, but leaves the statement in a state that enables it to be executed 
     * again.
     * 
     * <p>This method is useful for database drivers that do not support executing 
     * a HulkMVC_DOStmt object when a previously executed HulkMVC_DOStmt object 
     * still has unfetched rows. If your database driver suffers from this 
     * limitation, the problem may manifest itself in an out-of-sequence error.</p>
     * 
     * <p>{@link HulkMVC_DOStmt::closeCursor()} is implemented either as an optional 
     * driver specific method (allowing for maximum efficiency), or as the generic 
     * {@link HulkMVC_DO} fallback if no driver specific function is installed. The 
     * generic fallback is semantically the same as writing the following code in 
     * your PHP script:
     * 
     * </code>
     * <?php
     * do {
     * while ($stmt->fetch())
     *    ;
     * if (!$stmt->nextRowset())
     *     break;
     * 
     * } while (true);
     * ?>
     * </code>
     * </p>
     * 
     * @return bool Returns TRUE on success or FALSE on failure.
     * @see HulkMVC_DOStmt::execute()
     */
    public function closeCursor();
    
    /**
     * Gets the number of columns in the result set represented by the 
     * HulkMVC_DOStmt object.
     * 
     * <p>If the HulkMVC_DOStmt object was returned from {@link HulkMVC_DO::query()},
     * the column count is immediately available.</p>
     * 
     * <p>If the HulkMVC_DOStmt object was returned from 
     * {@link HulkMVC_DO->prepare()}, an accurate column count will not be 
     * available until you invoke {HulkMVC_DOStmt::execute()}.</p>
     * 
     * @return int Returns the number of columns in the result set represented by 
     * the HulkMVC_DOStmt object. If there is no result set, returns 0.
     * @see HulkMVC_DO::prepare()
     * @see HulkMVC_DOStmt::execute()
     * @see HulkMVC_DOStmt::rowCount()
     */
    public function columnCount();
    
    /**
     * Fetch the SQLSTATE associated with the last operation on the statement 
     * handle.
     *
     * @return string Error codes for operations performed with HulkMVC_DOStmt 
     * objects.
     * @see HulkMVC_DO::errorCode()
     * @see HulkMVC_DO::errorInfo()
     * @see HulkMVC_DOStmt::errorInfo()
     */
    public function errorCode();

    /**
     * Fetchs extended error information associated with the last operation on 
     * the statement handle.
     *
     * @return array Returns an array of error information about the last operation 
     * performed by this statement handle. The array consists of the following fields:
     * Element     Information
     *    0            SQLSTATE error code (defined in the ANSI SQL standard).
     *    1            Driver-specific error code.
     *    2            Driver-specific error message.
     * @see HulkMVC_DO::errorCode()
     * @see HulkMVC_DO::errorInfo()
     * @see HulkMVC_DOStmt::errorCode()
     */
    public function errorInfo();
    
    /**
     * Executes a prepared statement.
     *
     * @param mixed $input_parameters An array of values with as many elements as 
     * there are bound parameters in the SQL statement being executed.
     * @return bool Returns TRUE on success or FALSE on failure.
     * @see HulkMVC_DO::prepare()
     * @see HulkMVC_DOStmt::bindParam()
     * @see HulkMVC_DOStmt::fetch()
     * @see HulkMVC_DOStmt::fetchAll()
     * @see HulkMVC_DOStmt::fetchColumn()
     */
    public function execute($input_parameters);
    
    /**
     * Fetches the next row from a result set.
     * 
     * @param int $fetch_style Optional.
     * Controls how the next row will be returned to the caller. This value must be 
     * one of the HulkMVC_DO::FETCH_* constants, defaulting to HulkMVC_DO::FETCH_BOTH.
     * @param int $cursor_orientation Optional.  For a HulkMVC_DOStmt object 
     * representing a scrollable cursor, this value determines which row will be 
     * returned to the caller. This value must be one of the HulkMVC_DO::FETCH_ORI_*
     * constants, defaulting to HulkMVC_DO::FETCH_ORI_NEXT. To request a scrollable 
     * cursor for your HulkMVC_DOStmt object, you must set the 
     * HulkMVC_DO::ATTR_CURSOR attribute to HulkMVC_DO::CURSOR_SCROLL when you 
     * prepare the SQL statement with {@link HulkMVC_DO::prepare()}.
     * @param int $cursor_offset Optional. For a HulkMVC_DOStmt object representing 
     * a scrollable cursor for which the cursor_orientation parameter is set to 
     * HulkMVC_DO::FETCH_ORI_ABS, this value specifies the absolute number of the 
     * row in the result set that shall be fetched.
     * @return mixed Return value of this function on success depends on the fetch 
     * type.  In all cases, FALSE is returned on failure.
     * @see HulkMVC_DO::execute()
     * @see HulkMVC_DOStmt::fetchAll()
     * @see HulkMVC_DOStmt::fetchColumn()
     * @see HulkMVC_DOStmt::fetchObject()
     * @see HulkMVC_DO::prepare()
     * @see HulkMVC_DOStmt::setFetchMode()
     */
    public function fetch($fetch_style, $cursor_orientation, $cursor_offset);
    
    /**
     * Fetchs an array containing all of the result set rows.
     *
     * @param int $fetch_style Optional.  Controls the contents of the returned 
     * array as documented in {@link HulkMVC_DOStmtement::fetch()}. Defaults to 
     * HulkMVC_DO::FETCH_BOTH.  To return an array consisting of all values of a 
     * single column from the result set, specify HulkMVC_DO::FETCH_COLUMN. You can 
     * specify which column you want with the column-index parameter.  To fetch 
     * only the unique values of a single column from the result set, bitwise-OR 
     * HulkMVC_DO::FETCH_COLUMN with HulkMVC_DO::FETCH_UNIQUE.  To return an 
     * associative array grouped by the values of a specified column, bitwise-OR 
     * HulkMVC_DO::FETCH_COLUMN with HulkMVC_DO::FETCH_GROUP.
     * @param int $column_index Optional.  Returns the indicated 0-indexed column 
     * when the value of fetch_style is HulkMVC_DO::FETCH_COLUMN. Defaults to 0.
     * @param unknown_type $ctor_args Optional.  Arguments of custom class 
     * constructor.
     * @return array Returns an array containing all of the remaining rows in the 
     * result set. The array represents each row as either an array of column 
     * values or an object with properties corresponding to each column name.  
     * <p>Using this method to fetch large result sets will result in a heavy demand
     *  on system and possibly network resources. Rather than retrieving all of 
     * the data and manipulating it in PHP, consider using the database server to 
     * manipulate the result sets. For example, use the WHERE and SORT BY clauses 
     * in SQL to restrict results before retrieving and processing them with 
     * PHP.</p> 
     * @see HulkMVC_DO::query()
     * @see HulkMVC_DOStmt::fetch()
     * @see HulkMVC_DOStmt::fetchColumn()
     * @see HulkMVC_DO::prepare()
     * @see HulkMVC_DOStmt::setFetchMode()
     */
    public function fetchAll($fetch_style, $column_index, $ctor_args);
    
    /**
     * Fetchs a single column from the next row of a result set.
     *
     * @param int $column_number Optioanl.  0-indexed number of the column you wish 
     * to retrieve from the row. If no value is supplied, 
     * {@link HulkMVC_DOStmtement::fetchColumn()} fetches the first column.
     * @return string Returns a single column in the next row of a result set.
     * @see HulkMVC_DO::query()
     * @see HulkMVC_DOStmt::fetch()
     * @see HulkMVC_DOStmt::fetchAll()
     * @see HulkMVC_DO::prepare()
     * @see HulkMVC_DOStmt::setFetchMode()
     */
    public function fetchColumn($column_number);
    
    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $class_name Optional.  Name of the created class, defaults to 
     * stdClass.
     * @param array $ctor_args Optional.  Elements of this array are passed to the 
     * constructor.
     * @return mixed Returns an instance of the required class with property names 
     * that correspond to the column names or FALSE in case of an error.
     * @see HulkMVC_DOStmt::fetch()
     */
    public function fetchObject($class_name, $ctor_args);
    
    /**
     * Gets a statement object attribute.
     *
     * @param int $attribute  Gets an attribute of the statement. Currently, no 
     * generic attributes exist but only driver specific:  
     * HulkMVC_DO::ATTR_CURSOR_NAME (Firebird and ODBC specific): Get the name 
     * of cursor for UPDATE ... WHERE CURRENT OF.
     * @return mixed Returns the attribute value.
     * @see HulkMVC_DO::getAttribute()
     * @see HulkMVC_DO::setAttribute()
     * @see HulkMVC_DOStmt::setAttribute()
     */
    public function getAttribute($attribute);
    
    /**
     * Gets the number of rows affected by the last affected by the last DELETE, 
     * INSERT, or UPDATE statement. 
     *
     * @return int Returns the number of rows.
     * @see HulkMVC_DOStmt::columnCount()
     * @see HulkMVC_DOStmt::fetchColumn()
     * @see HulkMVC_DO::query()
     */
    public function rowCount();
    
    /**
     *  Sets an attribute on the statement. 
     * 
     * <p> Currently, no generic attributes are set but only driver specific: 
     * HulkMVC_DO::ATTR_CURSOR_NAME (Firebird and ODBC specific): Set the name 
     * of cursor for UPDATE ... WHERE CURRENT OF.</p>
     *
     * @param int $attribute The attribute value top set.
     * @param mixed $value The value with which to set the attribute.
     * @return bool Returns TRUE on success or FALSE on failure.
     * @see HulkMVC_DO::getAttribute()
     * @see HulkMVC_DO::setAttribute()
     * @see HulkMVC_DOStmt::getAttribute()
     */
    public function setAttribute($attribute, $value);
    
    /**
     * Sets the default fetch mode for this statement.
     *
     * @param int $mode The fetch mode must be one of the HulkMVC_DO::FETCH_* 
     * constants.
     * @param mixed Optional.  Depending on the $mode, this associates the value
     * with the type.  If HulkMVC_DO::FETCH_COLUMN is presented then $into is the
     * column number.  If HulkMVC_DO::FETCH_CLASS is presented then $into is the
     * class name and the $ctor_args are passed to its consturctor.  If 
     * HulkMVC_DO::FETCH_INTO is presented then $into is the object to insert the
     * values.
     * @return bool Returns 1 on success or FALSE on failure.
     */
    public function setFetchMode($mode, $into, $ctor_args);
    
}
?>