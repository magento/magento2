<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Currency;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Framework\Currency
 */
class CurrencyTest extends TestCase
{
    public function testConstruct()
    {
        $frontendCache = $this->createMock(FrontendInterface::class);
        $lowLevelFrontend = $this->createMock(\Zend_Cache_Core::class);
        /** @var CacheInterface|MockObject $appCache */
        $appCache = $this->createMock(CacheInterface::class);
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
