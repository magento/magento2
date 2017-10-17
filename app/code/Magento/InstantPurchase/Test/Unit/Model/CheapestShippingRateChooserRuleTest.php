<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InstantPurchase\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\InstantPurchase\Model\CheapestShippingRateChooserRule;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CheapestShippingRateChooserRuleTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CheapestShippingRateChooserRule
     */
    private $shippingRateChooser;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->shippingRateChooser = $objectManager->getObject(CheapestShippingRateChooserRule::class);
    }

    public function testChoose()
    {
        $shippingRates = [
            ['code' => 'expensive_rate', 'price' => 100],
            ['code' => 'cheap_rate', 'price' => 10]
        ];
        $chosenCode = 'cheap_rate';
        $result = $this->shippingRateChooser->choose($shippingRates);
        $this->assertEquals($result, $chosenCode);
    }
}
