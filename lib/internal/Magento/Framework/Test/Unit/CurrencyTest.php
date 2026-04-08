<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Currency;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Test for Magento\Framework\Currency
 */
class CurrencyTest extends TestCase
{
    public function testConstruct()
    {
        $frontendCache = $this->createMock(FrontendInterface::class);
        $cachePoolMock = $this->createMock(CacheItemPoolInterface::class);
        /** @var CacheInterface|MockObject $appCache */
        $appCache = $this->createMock(CacheInterface::class);
        $frontendCache->expects($this->once())->method('getLowLevelFrontend')->willReturn($cachePoolMock);
        $appCache->expects($this->once())
            ->method('getFrontend')
            ->willReturn($frontendCache);

        // Create new currency object
        $currency = new Currency($appCache, null, 'en_US');
        $this->assertEquals($cachePoolMock, $currency->getCache());
        $this->assertEquals('USD', $currency->getShortName());
    }
}
