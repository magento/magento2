<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\Currency;

/**
 * Test for Magento\Framework\Currency
 */
class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $translateCache = $this->getMock('Magento\Framework\App\Cache\Type\Translate', [], [], '', false, false);
        $lowLevelFrontend = $this->getMock('Zend_Cache_Core', [], [], '', false, false);
        /** @var \Magento\Framework\App\Cache\Type\Translate|\PHPUnit_Framework_MockObject_MockObject $appCache */
        $translateCache->expects($this->once())->method('getLowLevelFrontend')->willReturn($lowLevelFrontend);

        // Create new currency object
        $currency = new Currency($translateCache, null, 'en_US');
        $this->assertEquals($lowLevelFrontend, $currency->getCache());
        $this->assertEquals('USD', $currency->getShortName());
    }
}
