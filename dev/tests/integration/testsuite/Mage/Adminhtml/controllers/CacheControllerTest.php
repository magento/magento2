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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_CacheControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/cache/application_cache.php
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/cache/non_application_cache.php
     */
    public function testFlushAllAction()
    {
        $this->dispatch('backend/admin/cache/flushAll');

        /** @var $cache Mage_Core_Model_Cache */
        $cache = Mage::getModel('Mage_Core_Model_Cache');
        /** @var $cachePool Mage_Core_Model_Cache_Frontend_Pool */
        $this->assertFalse($cache->load('APPLICATION_FIXTURE'));

        $cachePool = Mage::getModel('Mage_Core_Model_Cache_Frontend_Pool');
        /** @var $cacheFrontend Magento_Cache_FrontendInterface */
        foreach ($cachePool as $cacheFrontend) {
            $this->assertFalse($cacheFrontend->getBackend()->load('NON_APPLICATION_FIXTURE'));
        }
    }

    /**
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/cache/application_cache.php
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/cache/non_application_cache.php
     */
    public function testFlushSystemAction()
    {
        $this->dispatch('backend/admin/cache/flushSystem');

        /** @var $cache Mage_Core_Model_Cache */
        $cache = Mage::getModel('Mage_Core_Model_Cache');
        /** @var $cachePool Mage_Core_Model_Cache_Frontend_Pool */
        $this->assertFalse($cache->load('APPLICATION_FIXTURE'));

        $cachePool = Mage::getModel('Mage_Core_Model_Cache_Frontend_Pool');
        /** @var $cacheFrontend Magento_Cache_FrontendInterface */
        foreach ($cachePool as $cacheFrontend) {
            $this->assertSame('non-application cache data',
                $cacheFrontend->getBackend()->load('NON_APPLICATION_FIXTURE'));
        }
    }

    /**
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/cache/all_types_disabled.php
     * @dataProvider massActionsDataProvider
     * @param array $typesToEnable
     */
    public function testMassEnableAction($typesToEnable = array())
    {
        $this->getRequest()->setParams(array('types' => $typesToEnable));
        $this->dispatch('backend/admin/cache/massEnable');

        $types = array_keys(Mage::getModel('Mage_Core_Model_Cache')->getTypes());
        /** @var $cacheTypes Mage_Core_Model_Cache_Types */
        $cacheTypes = Mage::getModel('Mage_Core_Model_Cache_Types');
        foreach ($types as $type) {
            if (in_array($type, $typesToEnable)) {
                $this->assertTrue($cacheTypes->isEnabled($type), "Type '$type' has not been enabled");
            } else {
                $this->assertFalse($cacheTypes->isEnabled($type), "Type '$type' must remain disabled");
            }
        }
    }

    /**
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/cache/all_types_enabled.php
     * @dataProvider massActionsDataProvider
     * @param array $typesToDisable
     */
    public function testMassDisableAction($typesToDisable = array())
    {
        $this->getRequest()->setParams(array('types' => $typesToDisable));
        $this->dispatch('backend/admin/cache/massDisable');

        $types = array_keys(Mage::getModel('Mage_Core_Model_Cache')->getTypes());
        /** @var $cacheTypes Mage_Core_Model_Cache_Types */
        $cacheTypes = Mage::getModel('Mage_Core_Model_Cache_Types');
        foreach ($types as $type) {
            if (in_array($type, $typesToDisable)) {
                $this->assertFalse($cacheTypes->isEnabled($type), "Type '$type' has not been disabled");
            } else {
                $this->assertTrue($cacheTypes->isEnabled($type), "Type '$type' must remain enabled");
            }
        }
    }

    /**
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/cache/all_types_invalidated.php
     * @dataProvider massActionsDataProvider
     * @param array $typesToRefresh
     */
    public function testMassRefreshAction($typesToRefresh = array())
    {
        $this->getRequest()->setParams(array('types' => $typesToRefresh));
        $this->dispatch('backend/admin/cache/massRefresh');

        /** @var $cache Mage_Core_Model_Cache */
        $cache = Mage::getModel('Mage_Core_Model_Cache');
        $invalidatedTypes = array_keys($cache->getInvalidatedTypes());
        $failed = array_intersect($typesToRefresh, $invalidatedTypes);
        $this->assertEmpty($failed, 'Could not refresh following cache types: ' . join(', ', $failed));

    }

    /**
     * @return array
     */
    public function massActionsDataProvider()
    {
        return array(
            'no types'           => array(array()),
            'existing types'     => array(array('config', 'layout', 'block_html')),
        );
    }

    /**
     * @dataProvider massActionsInvalidTypesDataProvider
     * @param $action
     */
    public function testMassActionsInvalidTypes($action)
    {
        $this->getRequest()->setParams(array('types' => array('invalid_type_1', 'invalid_type_2', 'config')));
        $this->dispatch('backend/admin/cache/' . $action);
        $this->assertSessionMessages(
            $this->contains("Specified cache type(s) don't exist: invalid_type_1, invalid_type_2"),
            Mage_Core_Model_Message::ERROR
        );
    }

    /**
     * @return array
     */
    public function massActionsInvalidTypesDataProvider()
    {
        return array(
            'enable'  => array('massEnable'),
            'disable' => array('massDisable'),
            'refresh' => array('massRefresh'),
        );
    }
}
