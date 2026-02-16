<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\Controller\Adminhtml;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @magentoAppArea adminhtml
 */
class CacheTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/application_cache.php
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/non_application_cache.php
     */
    public function testFlushAllAction()
    {
        /** @var $cache \Magento\Framework\App\Cache */
        $cache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Cache::class
        );
        $this->assertNotEmpty($cache->load('APPLICATION_FIXTURE'));

        $this->dispatch('backend/admin/cache/flushAll');

        /** @var $cachePool \Magento\Framework\App\Cache\Frontend\Pool */
        $this->assertFalse($cache->load('APPLICATION_FIXTURE'));

        $cachePool = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Cache\Frontend\Pool::class
        );
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($cachePool as $cacheFrontend) {
            $this->assertFalse($cacheFrontend->load('NON_APPLICATION_FIXTURE'));
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
        $cache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Cache::class
        );
        /** @var $cachePool \Magento\Framework\App\Cache\Frontend\Pool */
        $this->assertFalse($cache->load('APPLICATION_FIXTURE'));

        $cachePool = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Cache\Frontend\Pool::class
        );
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($cachePool as $cacheFrontend) {
            $this->assertSame(
                'non-application cache data',
                $cacheFrontend->load('NON_APPLICATION_FIXTURE')
            );
        }
    }

    /**
     * @param $action
     */
    #[DataProvider('massActionsInvalidTypesDataProvider')]
    public function testMassActionsInvalidTypes($action)
    {
        $this->getRequest()->setParams(['types' => ['invalid_type_1', 'invalid_type_2', 'config']]);
        $this->dispatch('backend/admin/cache/' . $action);
        $this->assertSessionMessages(
            $this->containsEqual("These cache type(s) don&#039;t exist: invalid_type_1, invalid_type_2"),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @return array
     */
    public static function massActionsInvalidTypesDataProvider()
    {
        return [
            'enable' => ['massEnable'],
            'disable' => ['massDisable'],
            'refresh' => ['massRefresh']
        ];
    }
}
