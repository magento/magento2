<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\Setup\ModuleDataSetupInterface;

class DataSetupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Setup\Module\DataSetup::class
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

    public function testDeleteTableRow()
    {
        $this->expectException(\Magento\Framework\DB\Adapter\TableNotFoundException::class);
        $this->_model->deleteTableRow('setup/module', 'module', 'integration_test_fixture_setup');
    }

    public function testUpdateTableRowNameConversion()
    {
        $this->expectException(\Magento\Framework\DB\Adapter\TableNotFoundException::class);
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
        $this->assertInstanceOf(\Magento\Framework\Setup\DataCacheInterface::class, $this->_model->getSetupCache());
    }
}
