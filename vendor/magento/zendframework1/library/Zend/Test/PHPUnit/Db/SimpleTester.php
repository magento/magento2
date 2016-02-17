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
 * @see Zend_Test_PHPUnit_Db_Operation_Truncate
 */
#require_once "Zend/Test/PHPUnit/Db/Operation/Truncate.php";

/**
 * @see Zend_Test_PHPUnit_Db_Operation_Insert
 */
#require_once "Zend/Test/PHPUnit/Db/Operation/Insert.php";

/**
 * Simple Tester for Database Tests when the Abstract Test Case cannot be used.
 *
 * @uses       PHPUnit_Extensions_Database_DefaultTester
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Test_PHPUnit_Db_SimpleTester extends PHPUnit_Extensions_Database_DefaultTester
{
    /**
     * Creates a new default database tester using the given connection.
     *
     * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
     */
    public function __construct(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection)
    {
        if(!($connection instanceof Zend_Test_PHPUnit_Db_Connection)) {
            #require_once "Zend/Test/PHPUnit/Db/Exception.php";
            throw new Zend_Test_PHPUnit_Db_Exception("Not a valid Zend_Test_PHPUnit_Db_Connection instance, ".get_class($connection)." given!");
        }

        $this->connection = $connection;
        $this->setUpOperation = new PHPUnit_Extensions_Database_Operation_Composite(array(
            new Zend_Test_PHPUnit_Db_Operation_Truncate(),
            new Zend_Test_PHPUnit_Db_Operation_Insert(),
        ));
        $this->tearDownOperation = PHPUnit_Extensions_Database_Operation_Factory::NONE();
    }

    /**
     * Set Up the database using the given Dataset and the SetUp strategy "Truncate, then Insert"
     *
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
     */
    public function setUpDatabase(PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
    {
        $this->setDataSet($dataSet);
        $this->onSetUp();
    }
}
