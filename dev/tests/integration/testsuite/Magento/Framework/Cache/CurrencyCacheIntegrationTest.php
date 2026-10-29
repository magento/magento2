<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Currency;
use Magento\Framework\Currency\Data\Currency as CurrencyData;
use Magento\Framework\Cache\LowLevelFrontendInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies currency cache wiring through Magento's real application cache frontend.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CurrencyCacheIntegrationTest extends TestCase
{
    public function testCurrencyUsesConfiguredLowLevelFrontendAndClearsTaggedData(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $appCache = $objectManager->get(CacheInterface::class);
        $currency = $objectManager->create(Currency::class, ['options' => 'USD']);
        $lowLevelFrontend = $appCache->getFrontend()->getLowLevelFrontend();
        $id = 'integration_currency_cache_' . uniqid();

        try {
            $this->assertInstanceOf(LowLevelFrontendInterface::class, CurrencyData::getCache());
            $this->assertTrue($lowLevelFrontend->save(
                'currency-cache-value',
                $id,
                ['INTEGRATION_CURRENCY_CACHE'],
                3600
            ));
            $this->assertSame('currency-cache-value', $lowLevelFrontend->load($id));

            CurrencyData::clearCache('INTEGRATION_CURRENCY_CACHE');

            $this->assertFalse($lowLevelFrontend->load($id));
            $this->assertSame('USD', $currency->getShortName());
        } finally {
            $lowLevelFrontend->remove($id);
            CurrencyData::removeCache();
        }
    }
}
