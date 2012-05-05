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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Resource_Helper_Mysql4Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Resource_Helper_Mysql4
     */
    protected $_model;

    /**
     * @var Varien_Db_Select
     */
    protected $_select;

    protected function setUp()
    {
        if (Magento_Test_Bootstrap::getInstance()->getDbVendorName() != 'mysql') {
            $this->markTestSkipped('Test is designed to run on MySQL only.');
        }
        $this->_model = new Mage_Core_Model_Resource_Helper_Mysql4('core');
        $collection = new Mage_Core_Model_Resource_Store_Collection();
        $this->_select = $collection->getSelect();
    }

    public function testCastField()
    {
        $this->assertEquals('test', $this->_model->castField('test'));
    }

    public function testPrepareColumn()
    {
        $column = $this->_model->prepareColumn('test');
        $this->assertInstanceOf('Zend_Db_Expr', $column);
        $this->assertEquals('test', (string) $column);
    }

    public function testGetQueryUsingAnalyticFunction()
    {
        $select = $this->_model->getQueryUsingAnalyticFunction($this->_select);
        $this->assertEquals((string) $this->_select, $select);
    }

    public function testGetInsertFromSelectUsingAnalytic()
    {
        $insert = $this->_model->getInsertFromSelectUsingAnalytic($this->_select, 'core_store', array('store_id'));
        $this->assertStringStartsWith('INSERT', $insert);
        $this->assertContains('core_store', $insert);
        $this->assertContains('store_id', $insert);
    }

    public function testLimitUnion()
    {
        $select = $this->_model->limitUnion($this->_select);
        $this->assertEquals((string) $this->_select, (string)$select);
    }

    public function testPrepareColumnsList()
    {
        $columns = $this->_model->prepareColumnsList($this->_select);
        $this->assertContains('STORE_ID', array_keys($columns));
    }

    public function testAddGroupConcatColumn()
    {
        $select = (string)$this->_model->addGroupConcatColumn($this->_select, 'test_alias', 'store_id');
        $this->assertContains('GROUP_CONCAT', $select);
        $this->assertContains('test_alias', $select);
    }

    public function testGetDateDiff()
    {
        $diff = $this->_model->getDateDiff('2011-01-01', '2011-01-01');
        $this->assertInstanceOf('Zend_Db_Expr', $diff);
        $this->assertContains('TO_DAYS', (string) $diff);
    }

    public function testAddLikeEscape()
    {
        $value = $this->_model->addLikeEscape('test');
        $this->assertInstanceOf('Zend_Db_Expr', $value);
        $this->assertContains('test', (string) $value);
    }
}
