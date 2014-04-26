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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\FrontController\Plugin;

class InstallTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Module\Setup
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Module\Setup',
            array('resourceName' => 'default_setup', 'moduleName' => 'Magento_Core')
        );
    }

    public function testApplyAllDataUpdates()
    {
        /*reset versions*/
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Module\ResourceInterface'
        )->setDbVersion(
            'adminnotification_setup',
            false
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Module\ResourceInterface'
        )->setDataVersion(
            'adminnotification_setup',
            false
        );
        $this->_model->deleteTableRow('core_resource', 'code', 'adminnotification_setup');
        $this->_model->getConnection()->dropTable($this->_model->getTable('adminnotification_inbox'));
        $this->_model->getConnection()->dropTable($this->_model->getTable('admin_system_messages'));
        /** @var \Magento\Framework\Cache\FrontendInterface $cache */
        $cache = $this->_objectManager->get('Magento\Framework\App\Cache\Type\Config');
        $cache->clean();

        try {
            /* This triggers plugin to be executed */
            $this->dispatch('index/index');
        } catch (\Exception $e) {
            $this->fail("Impossible to continue other tests, because database is broken: {$e}");
        }

        try {
            $tableData = $this->_model->getConnection()->describeTable(
                $this->_model->getTable('adminnotification_inbox')
            );
            $this->assertNotEmpty($tableData);
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
}
