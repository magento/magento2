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
namespace Magento\Backend\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class CacheTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/application_cache.php
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/non_application_cache.php
     */
    public function testFlushAllAction()
    {
        $this->dispatch('backend/admin/cache/flushAll');

        /** @var $cache \Magento\Framework\App\Cache */
        $cache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Framework\App\Cache');
        /** @var $cachePool \Magento\Framework\App\Cache\Frontend\Pool */
        $this->assertFalse($cache->load('APPLICATION_FIXTURE'));

        $cachePool = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Cache\Frontend\Pool'
        );
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($cachePool as $cacheFrontend) {
            $this->assertFalse($cacheFrontend->getBackend()->load('NON_APPLICATION_FIXTURE'));
        }
    }

    /**
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/application_cache.php
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/non_application_cache.php
     */
    public function testFlushSystemAction()
    {
        $this->dispatch('backend/admin/cache/flushSystem');

        /** @var $cache \Magento\Framework\App\Cache */
        $cache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Framework\App\Cache');
        /** @var $cachePool \Magento\Framework\App\Cache\Frontend\Pool */
        $this->assertFalse($cache->load('APPLICATION_FIXTURE'));

        $cachePool = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Cache\Frontend\Pool'
        );
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($cachePool as $cacheFrontend) {
            $this->assertSame(
                'non-application cache data',
                $cacheFrontend->getBackend()->load('NON_APPLICATION_FIXTURE')
            );
        }
    }

    /**
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_disabled.php
     * @dataProvider massActionsDataProvider
     * @param array $typesToEnable
     */
    public function testMassEnableAction($typesToEnable = array())
    {
        $this->getRequest()->setParams(array('types' => $typesToEnable));
        $this->dispatch('backend/admin/cache/massEnable');

        /** @var  \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList */
        $cacheTypeList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Cache\TypeListInterface'
        );
        $types = array_keys($cacheTypeList->getTypes());
        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Cache\StateInterface'
        );
        foreach ($types as $type) {
            if (in_array($type, $typesToEnable)) {
                $this->assertTrue($cacheState->isEnabled($type), "Type '{$type}' has not been enabled");
            } else {
                $this->assertFalse($cacheState->isEnabled($type), "Type '{$type}' must remain disabled");
            }
        }
    }

    /**
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_enabled.php
     * @dataProvider massActionsDataProvider
     * @param array $typesToDisable
     */
    public function testMassDisableAction($typesToDisable = array())
    {
        $this->getRequest()->setParams(array('types' => $typesToDisable));
        $this->dispatch('backend/admin/cache/massDisable');

        /** @var  \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList */
        $cacheTypeList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Cache\TypeListInterface'
        );
        $types = array_keys($cacheTypeList->getTypes());
        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Cache\StateInterface'
        );
        foreach ($types as $type) {
            if (in_array($type, $typesToDisable)) {
                $this->assertFalse($cacheState->isEnabled($type), "Type '{$type}' has not been disabled");
            } else {
                $this->assertTrue($cacheState->isEnabled($type), "Type '{$type}' must remain enabled");
            }
        }
    }

    /**
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_invalidated.php
     * @dataProvider massActionsDataProvider
     * @param array $typesToRefresh
     */
    public function testMassRefreshAction($typesToRefresh = array())
    {
        $this->getRequest()->setParams(array('types' => $typesToRefresh));
        $this->dispatch('backend/admin/cache/massRefresh');

        /** @var $cacheTypeList \Magento\Framework\App\Cache\TypeListInterface */
        $cacheTypeList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Cache\TypeListInterface'
        );
        $invalidatedTypes = array_keys($cacheTypeList->getInvalidated());
        $failed = array_intersect($typesToRefresh, $invalidatedTypes);
        $this->assertEmpty($failed, 'Could not refresh following cache types: ' . join(', ', $failed));
    }

    /**
     * @return array
     */
    public function massActionsDataProvider()
    {
        return array(
            'no types' => array(array()),
            'existing types' => array(
                array(
                    \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
                    \Magento\Framework\App\Cache\Type\Layout::TYPE_IDENTIFIER,
                    \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER
                )
            )
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
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @return array
     */
    public function massActionsInvalidTypesDataProvider()
    {
        return array(
            'enable' => array('massEnable'),
            'disable' => array('massDisable'),
            'refresh' => array('massRefresh')
        );
    }
}
