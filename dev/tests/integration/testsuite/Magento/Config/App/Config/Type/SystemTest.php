<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Type;

use Magento\Config\Model\Config\Factory;
use Magento\Framework\Lock\Backend\Cache;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Config/_files/config_data.php
 * @magentoAppIsolation enabled
 * @magentoCache config enabled
 */
class SystemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var System
     */
    private $system;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->system = $this->createSystemConfig();
    }

    public function testGetValueForDefaultScope()
    {
        $this->assertEquals(
            'value1.db.default.test',
            $this->system->get('default/web/test/test_value_1')
        );
    }

    public function testGetValueForWebsiteScope()
    {
        $this->assertEquals(
            'value1.db.website_base.test',
            $this->system->get('websites/base/web/test/test_value_1')
        );
    }

    public function testGetValueForStoreScope()
    {
        $this->assertEquals(
            'value1.db.store_default.test',
            $this->system->get('stores/default/web/test/test_value_1')
        );
    }

    public function testCachingDoesNotBreakValueRetrievalForStoreScope()
    {
        // First uncached call to configuration
        $this->createSystemConfig()->get('stores/default/web/test/test_value_1');

        // Second call after cache data is stored
        $this->assertEquals(
            'value1.db.store_default.test',
            $this->createSystemConfig()->get('stores/default/web/test/test_value_1')
        );
    }

    public function testCachingDoesNotBreakValueRetrievalForWebsiteScope()
    {
        // First uncached call to configuration
        $this->createSystemConfig()->get('websites/base/web/test/test_value_1');

        // Second call after cache data is stored
        $this->assertEquals(
            'value1.db.website_base.test',
            $this->createSystemConfig()->get('websites/base/web/test/test_value_1')
        );
    }

    public function testCachingDoesNotBreakValueRetrievalForDefaultScope()
    {
        // First uncached call to configuration
        $this->createSystemConfig()->get('default/web/test/test_value_1');

        // Second call after cache data is stored
        $this->assertEquals(
            'value1.db.default.test',
            $this->createSystemConfig()->get('default/web/test/test_value_1')
        );
    }

    public function testClearingCachePrefixAndLockingItReturnsStaleCachedValue()
    {
        // First uncached call to configuration
        $this->createSystemConfig()->get('default/web/test/test_value_1');

        $this->clearConfigCachePrefix();
        $this->accessLock()->lock('SYSTEM_CONFIG');
        // Second call after cache data is stored
        $configValue = $this->createSystemConfig()->get('default/web/test/test_value_1');
        $this->accessLock()->unlock('SYSTEM_CONFIG');

        $this->assertEquals(
            'value1.db.default.test',
            $configValue
        );
    }

    public function testClearingConfigurationCacheAndLockingFallsBackToStaleCache()
    {
        // First uncached call to configuration
        $this->createSystemConfig()->get('stores/default/web/test/test_value_1');

        $this->accessCacheFrontend()->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [System::CACHE_TAG]);

        $this->accessLock()->lock('SYSTEM_CONFIG');
        // Second call after cache data is stored
        $configValue = $this->createSystemConfig()->get('stores/default/web/test/test_value_1');
        $this->accessLock()->unlock('SYSTEM_CONFIG');

        $this->assertEquals(
            'value1.db.store_default.test',
            $configValue
        );
    }

    public function testChangingConfigurationValueRefreshesACache()
    {
        $systemConfig = $this->objectManager->get(System::class);

        // First uncached call to configuration
        $systemConfig->get('stores/default/web/test/test_value_1');

        /** @var Factory $configFactory */
        $configFactory = $this->objectManager->create(Factory::class);
        $config = $configFactory->create();
        $config->setScope(ScopeInterface::SCOPE_STORES);
        $config->setStore('default');

        $config->setDataByPath('web/test/test_value_1', 'new_uncached_value');
        $config->save();

        $this->assertEquals(
            'new_uncached_value',
            $systemConfig->get('stores/default/web/test/test_value_1')
        );
    }

    private function clearConfigCachePrefix()
    {
        $cache = $this->accessCacheFrontend();

        $cache->remove(System::STALE_CACHE_KEY_FOR_PREFIX);
    }

    private function accessCacheFrontend(): FrontendInterface
    {
        return $this->objectManager->get(Config::class);
    }

    private function createSystemConfig(): System
    {
        return $this->objectManager->create(System::class);
    }

    private function accessLock(): LockManagerInterface
    {
        return $this->objectManager->get(Cache::class);
    }
}
