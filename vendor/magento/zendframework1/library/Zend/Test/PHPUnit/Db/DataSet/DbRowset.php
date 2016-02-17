<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Db_Table_Rowset_Abstract
 */
#require_once "Zend/Db/Table/Rowset/Abstract.php";

/**
 * Use a Zend_Db Rowset as a datatable for assertions with other PHPUnit Database extension tables.
 *
 * @uses       PHPUnit_Extensions_Database_DataSet_AbstractTable
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Test_PHPUnit_Db_DataSet_DbRowset extends PHPUnit_Extensions_Database_DataSet_AbstractTable
{
    /**
     * Construct Table object from a Zend_Db_Table_Rowset
     *
     * @param Zend_Db_Table_Rowset_Abstract $rowset
     * @param string $tableName
     */
    public function __construct(Zend_Db_Table_Rowset_Abstract $rowset, $tableName = null)
    {
        if($tableName == null) {
            $table = $rowset->getTable();
            if($table !== null) {
                $tableName = $table->info('name');
            } else {
                #require_once "Zend/Test/PHPUnit/Db/Exception.php";
                throw new Zend_Test_PHPUnit_Db_Exception(
                    'No table name was given to Rowset Table and table name cannot be infered from the table, '.
                    'because the rowset is disconnected from database.'
                );
            }
        }

        $this->data = $rowset->toArray();

        $columns = array();
        if(isset($this->data[0]) > 0) {
            $columns = array_keys($this->data[0]);
        } else if($rowset->getTable() != null) {
            $columns = $rowset->getTable()->info('cols');
        }

        $this->tableName = $tableName;
        $this->tableMetaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($this->tableName, $columns);
    }
}
