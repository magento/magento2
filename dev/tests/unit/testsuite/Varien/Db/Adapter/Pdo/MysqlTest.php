<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Varien
 * @package     Varien_Data
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Varien_Db_Adapter_Pdo_Mysql class test
 */
class Varien_Db_Adapter_Pdo_MysqlTest extends PHPUnit_Framework_TestCase
{
    /**
     * Custom error handler message
     */
    const CUSTOM_ERROR_HANDLER_MESSAGE = 'Custom error handler message';

    /**
     * Adapter for test
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    private $_adapter;

    /*
     * Mock DB adapter for DDL query tests
     */
    private $_mockAdapter;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->_mockAdapter = $this->getMock(
            'Varien_Db_Adapter_Pdo_Mysql',
            array('beginTransaction', 'getTransactionLevel'),
            array(), '', false
        );

        $this->_mockAdapter->expects($this->any())
             ->method('getTransactionLevel')
             ->will($this->returnValue(1));

        $this->_adapter = $this->getMock(
            'Varien_Db_Adapter_Pdo_Mysql',
            array('_connect', '_beginTransaction', '_commit', '_rollBack'),
            array(
                'dbname' => 'not_exists',
                'username' => 'not_valid',
                'password' => 'not_valid',
            ),
            '',
            false
        );

        $profiler = $this->getMock(
            'Zend_Db_Profiler'
        );

        $resourceProperty = new ReflectionProperty(
                get_class($this->_adapter),
                '_profiler'
        );
        $resourceProperty->setAccessible(true);
        $resourceProperty->setValue($this->_adapter, $profiler);
    }

    /**
     * Test result for bigint
     *
     * @dataProvider bigintResultProvider
     */
    public function testPrepareColumnValueForBigint($value, $expectedResult)
    {
        $result = $this->_adapter->prepareColumnValue(
            array('DATA_TYPE' => 'bigint'),
            $value
        );
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data Provider for testPrepareColumnValueForBigint
     */
    public function bigintResultProvider()
    {
        return array(
            array(1, 1),
            array(0, 0),
            array(-1, -1),
            array(1.0, 1),
            array(0.0, 0),
            array(-1.0, -1),
            array(1e-10, 0),
            array(7.9, 8),
            array(PHP_INT_MAX, PHP_INT_MAX),
            array(2147483647 + 1, '2147483648'),
            array(9223372036854775807 + 1, '9223372036854775808'),
            array(9223372036854775807, '9223372036854775807'),
            array(9223372036854775807.3423424234, '9223372036854775807'),
            array(2147483647 * pow(10, 10)+12, '21474836470000000012'),
            array(9223372036854775807 * pow(10, 10)+12, '92233720368547758080000000000'),
            array((0.099999999999999999999999995+0.2+0.3+0.4+0.5)*10, '15'),
            array('21474836470000000012', '21474836470000000012'),
            array(0x5468792130ABCDEF, '6082244480221302255')
        );
    }

    /**
     * Test not DDL query inside transaction
     *
     * @dataProvider sqlQueryProvider
     */
    public function testCheckNotDdlTransaction($query)
    {
        try {
            $this->_mockAdapter->query($query);
        } catch (Exception $e) {
            $this->assertNotContains($e->getMessage(), Varien_Db_Adapter_Interface::ERROR_DDL_MESSAGE);
        }

        $select = new Zend_Db_Select($this->_mockAdapter);
        $select->from('user');
        try {
            $this->_mockAdapter->query($select);
        } catch (Exception $e) {
            $this->assertNotContains($e->getMessage(), Varien_Db_Adapter_Interface::ERROR_DDL_MESSAGE);
        }
    }

    /**
     * Test DDL query inside transaction in Developer mode
     *
     * @dataProvider ddlSqlQueryProvider
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessage DDL statements are not allowed in transactions
     */
    public function testCheckDdlTransaction($ddlQuery)
    {
        $this->_mockAdapter->query($ddlQuery);
    }

    /**
     * Data Provider for testCheckDdlTransaction
     */
    public static function ddlSqlQueryProvider()
    {
        return array(
            array('CREATE table user sasdasd'),
            array('ALTER table user'),
            array('TRUNCATE table user'),
            array('RENAME table user'),
            array('DROP table user'),
        );
    }

    /**
     * Data Provider for testCheckNotDdlTransaction
     */
    public static function sqlQueryProvider()
    {
        return array(
            array('SELECT * FROM user'),
            array('UPDATE user'),
            array('DELETE from user'),
            array('INSERT into user'),
        );
    }

    /**
     * Test Asymmetric transaction rollback failure
     */
    public function testAsymmetricRollBackFailure()
    {
        try {
            $this->_adapter->rollBack();
            throw new Exception('Test Failed!');
        } catch (Exception $e) {
            $this->assertEquals(
                Varien_Db_Adapter_Interface::ERROR_ASYMMETRIC_ROLLBACK_MESSAGE,
                $e->getMessage()
            );
        }
    }

    /**
     * Test Asymmetric transaction commit failure
     */
    public function testAsymmetricCommitFailure()
    {
        try {
            $this->_adapter->commit();
            throw new Exception('Test Failed!');
        } catch (Exception $e) {
            $this->assertEquals(
                Varien_Db_Adapter_Interface::ERROR_ASYMMETRIC_COMMIT_MESSAGE,
                $e->getMessage()
            );
        }
    }

    /**
     * Test Asymmetric transaction commit success
     */
    public function testAsymmetricCommitSuccess()
    {
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
        $this->_adapter->beginTransaction();
        $this->assertEquals(1, $this->_adapter->getTransactionLevel());
        $this->_adapter->commit();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test Asymmetric transaction rollback success
     */
    public function testAsymmetricRollBackSuccess()
    {
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
        $this->_adapter->beginTransaction();
        $this->assertEquals(1, $this->_adapter->getTransactionLevel());
        $this->_adapter->rollBack();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test successfull nested transaction
     */
    public function testNestedTransactionCommitSuccess()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');
        $this->_adapter->expects($this->once())
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_commit');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->assertEquals(3, $this->_adapter->getTransactionLevel());
        $this->_adapter->commit();
        $this->_adapter->commit();
        $this->_adapter->commit();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test successfull nested transaction
     */
    public function testNestedTransactionRollBackSuccess()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');
        $this->_adapter->expects($this->once())
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_rollBack');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->assertEquals(3, $this->_adapter->getTransactionLevel());
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test successfull nested transaction
     */
    public function testNestedTransactionLastRollBack()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');
        $this->_adapter->expects($this->once())
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_rollBack');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->assertEquals(3, $this->_adapter->getTransactionLevel());
        $this->_adapter->commit();
        $this->_adapter->commit();
        $this->_adapter->rollBack();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     */
    public function testIncompleteRollBackFailureOnCommit()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');

        try {
            $this->_adapter->beginTransaction();
            $this->_adapter->beginTransaction();
            $this->_adapter->rollBack();
            $this->_adapter->commit();
            throw new Exception('Test Failed!');
        } catch (Exception $e) {
            $this->assertEquals(
                Varien_Db_Adapter_Interface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE,
                $e->getMessage()
            );
            $this->_adapter->rollBack();
        }
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     */
    public function testIncompleteRollBackFailureOnBeginTransaction()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');

        try {
            $this->_adapter->beginTransaction();
            $this->_adapter->beginTransaction();
            $this->_adapter->rollBack();
            $this->_adapter->beginTransaction();
            throw new Exception('Test Failed!');
        } catch (Exception $e) {
            $this->assertEquals(
                Varien_Db_Adapter_Interface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE,
                $e->getMessage()
            );
            $this->_adapter->rollBack();
        }
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     */
    public function testSequentialTransactionsSuccess()
    {
        $this->_adapter->expects($this->exactly(4))
            ->method('_connect');
        $this->_adapter->expects($this->exactly(2))
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_rollBack');
        $this->_adapter->expects($this->once())
            ->method('_commit');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();

        $this->_adapter->beginTransaction();
        $this->_adapter->commit();
    }
}
