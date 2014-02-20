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
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Resource;

class SetupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Resource\Setup
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Resource\Setup',
            array('resourceName' => 'default_setup', 'moduleName' => 'Magento_Core')
        );
    }

    public function testSetTable()
    {
        $this->_model->setTable('test_name', 'test_real_name');
        $this->assertEquals('test_real_name', $this->_model->getTable('test_name'));
    }

    public function testApplyAllDataUpdates()
    {
        /*reset versions*/
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\Resource\Resource')
            ->setDbVersion('adminnotification_setup', false);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\Resource\Resource')
            ->setDataVersion('adminnotification_setup', false);
        $this->_model->deleteTableRow('core_resource', 'code', 'adminnotification_setup');
        $this->_model->getConnection()->dropTable($this->_model->getTable('adminnotification_inbox'));
        $this->_model->getConnection()->dropTable($this->_model->getTable('admin_system_messages'));
        /** @var $updater \Magento\Module\Updater */
        $updater = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Module\Updater');
        try {
            $updater->updateScheme();
            $updater->updateData();
        } catch (\Exception $e) {
            $this->fail("Impossible to continue other tests, because database is broken: {$e}");
        }
        $this->assertNotEmpty(
            $this->_model->getTableRow('core_resource', 'code', 'adminnotification_setup', 'version')
        );
        $this->assertNotEmpty(
            $this->_model->getTableRow('core_resource', 'code', 'adminnotification_setup', 'data_version')
        );
    }

    public function testUpdateTableRow()
    {
        $original = $this->_model->getTableRow('core_resource', 'code', 'adminnotification_setup', 'version');
        $this->_model->updateTableRow('core_resource', 'code', 'adminnotification_setup', 'version', 'test');
        $this->assertEquals(
            'test',
            $this->_model->getTableRow('core_resource', 'code', 'adminnotification_setup', 'version')
        );
        $this->_model->updateTableRow('core_resource', 'code', 'adminnotification_setup', 'version', $original);
    }

    public function testSetDeleteConfigData()
    {
        $select = $this->_model->getConnection()->select()
            ->from($this->_model->getTable('core_config_data'), 'value')
            ->where('path=?', 'my/test/path');

        $this->_model->setConfigData('my/test/path', 'test_value');
        $this->assertEquals('test_value', $this->_model->getConnection()->fetchOne($select));

        $this->_model->deleteConfigData('my/test/path', 'test');
        $this->assertNotEmpty($this->_model->getConnection()->fetchRow($select));

        $this->_model->deleteConfigData('my/test/path');
        $this->assertEmpty($this->_model->getConnection()->fetchRow($select));
    }

    /**
     * @expectedException \Zend_Db_Statement_Exception
     */
    public function testGetTableRow()
    {
        $this->assertNotEmpty($this->_model->getTableRow('core_resource', 'code', 'core_setup'));
        $this->_model->getTableRow('core/resource', 'code', 'core_setup');
    }

    /**
     * @expectedException \Zend_Db_Statement_Exception
     */
    public function testDeleteTableRow()
    {
        $this->_model->deleteTableRow('core/resource', 'code', 'integration_test_fixture_setup');
    }

    /**
     * @covers \Magento\Core\Model\Resource\Setup::updateTableRow
     * @expectedException \Zend_Db_Statement_Exception
     */
    public function testUpdateTableRowNameConversion()
    {
        $original = $this->_model->getTableRow('core_resource', 'code', 'core_setup', 'version');
        $this->_model->updateTableRow('core/resource', 'code', 'core_setup', 'version', $original);
    }

    public function testTableExists()
    {
        $this->assertTrue($this->_model->tableExists('core_website'));
        $this->assertFalse($this->_model->tableExists('core/website'));
    }
}
