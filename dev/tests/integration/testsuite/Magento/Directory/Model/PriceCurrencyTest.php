<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for PriceCurrency model.
 */
class PriceCurrencyTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->priceCurrency = Bootstrap::getObjectManager()->get(PriceCurrency::class);
    }

    /**
     * Check PriceCurrency::format() doesn't depend on currency rate configuration.
     * @return void
     */
    public function testFormat()
    {
        self::assertSame(
            '<span class="price">AFN10.00</span>',
            $this->priceCurrency->format(10, true, 2, null, 'AFN')
        );
    }
}
