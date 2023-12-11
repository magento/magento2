<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Setup\Lists;
use Magento\Framework\Validator\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @var array
     */
    protected $expectedCurrencies = [
        'USD' => 'US Dollar (USD)',
        'EUR' => 'Euro (EUR)',
        'UAH' => 'Ukrainian Hryvnia (UAH)',
        'GBP' => 'British Pound (GBP)'
    ];

    public function testIsValid()
    {
        $lists = $this->createMock(Lists::class);
        $lists->expects($this->any())->method('getCurrencyList')->willReturn($this->expectedCurrencies);
        $currency = new Currency($lists);
        $this->assertTrue($currency->isValid('EUR'));
    }
}
