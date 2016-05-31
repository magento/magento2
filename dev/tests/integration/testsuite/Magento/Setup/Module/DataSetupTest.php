<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\Setup\ModuleDataSetupInterface;

class DataSetupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Setup\Module\DataSetup'
        );
    }

    public function testUpdateTableRow()
    {
        $original = $this->_model->getTableRow('setup_module', 'module', 'Magento_AdminNotification', 'schema_version');
        $this->_model->updateTableRow('setup_module', 'module', 'Magento_AdminNotification', 'schema_version', 'test');
        $this->assertEquals(
            'test',
            $this->_model->getTableRow('setup_module', 'module', 'Magento_AdminNotification', 'schema_version')
        );
        $this->_model->updateTableRow(
            'setup_module',
            'module',
            'Magento_AdminNotification',
            'schema_version',
            $original
        );
    }

    /**
     * @expectedException \Zend_Db_Statement_Exception
     */
    public function testGetTableRow()
    {
        $this->assertNotEmpty($this->_model->getTableRow('setup_module', 'module', 'Magento_AdminNotification'));
        $this->_model->getTableRow('setup/module', 'module', 'Magento_AdminNotification');
    }

    /**
     * @expectedException \Zend_Db_Statement_Exception
     */
    public function testDeleteTableRow()
    {
        $this->_model->deleteTableRow('setup/module', 'module', 'integration_test_fixture_setup');
    }

    /**
     * @covers \Magento\Setup\Module\DataSetup::updateTableRow
     * @expectedException \Zend_Db_Statement_Exception
     */
    public function testUpdateTableRowNameConversion()
    {
        $original = $this->_model->getTableRow('setup_module', 'module', 'core_setup', 'schema_version');
        $this->_model->updateTableRow('setup/module', 'module', 'core_setup', 'schema_version', $original);
    }

    public function testTableExists()
    {
        $this->assertTrue($this->_model->tableExists('store_website'));
        $this->assertFalse($this->_model->tableExists('core/website'));
    }

    public function testGetSetupCache()
    {
        $this->assertInstanceOf('Magento\Framework\Setup\DataCacheInterface', $this->_model->getSetupCache());
    }
}
