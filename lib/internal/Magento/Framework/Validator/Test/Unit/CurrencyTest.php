<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit;

class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected $expectedCurrencies = [
        'USD',
        'EUR',
        'UAH',
        'GBP',
    ];

    public function testIsValid()
    {
        $lists = $this->createMock(\Magento\Framework\Setup\Lists::class);
        $lists->expects($this->any())->method('getCurrencyList')->will($this->returnValue($this->expectedCurrencies));
        $currency = new \Magento\Framework\Validator\Currency($lists);
        $this->assertEquals(true, $currency->isValid('EUR'));
    }
}
