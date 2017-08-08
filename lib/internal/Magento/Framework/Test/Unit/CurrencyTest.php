<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\Currency;

/**
 * Test for Magento\Framework\Currency
 */
class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $frontendCache = $this->createMock(\Magento\Framework\Cache\FrontendInterface::class);
        $lowLevelFrontend = $this->createMock(\Zend_Cache_Core::class);
        /** @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject $appCache */
        $appCache = $this->createMock(\Magento\Framework\App\CacheInterface::class);
        $frontendCache->expects($this->once())->method('getLowLevelFrontend')->willReturn($lowLevelFrontend);
        $appCache->expects($this->once())
            ->method('getFrontend')
            ->willReturn($frontendCache);

        // Create new currency object
        $currency = new Currency($appCache, null, 'en_US');
        $this->assertEquals($lowLevelFrontend, $currency->getCache());
        $this->assertEquals('USD', $currency->getShortName());
    }
}
