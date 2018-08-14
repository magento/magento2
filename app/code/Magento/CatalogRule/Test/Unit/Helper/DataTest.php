<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Helper object
     *
     * @var \Magento\CatalogRule\Helper\Data
     */
    protected $helper;

    protected function setUp()
    {
        $this->helper = (new ObjectManager($this))->getObject(\Magento\CatalogRule\Helper\Data::class);
    }

    /**
     * Test price rule calculation
     *
     * @param string $actionOperator
     * @param int|float $ruleAmount
     * @param int|float $price
     * @param int|float $expectedAmount
     *
     * @dataProvider calcPriceRuleDataProvider
     */
    public function testCalcPriceRule($actionOperator, $ruleAmount, $price, $expectedAmount)
    {
        $this->assertEquals($expectedAmount, $this->helper->calcPriceRule($actionOperator, $ruleAmount, $price));
    }

    /**
     * Data provider for cal price rule test
     *
     * @return array
     */
    public function calcPriceRuleDataProvider()
    {
        return [
            ['to_fixed', 10, 10, 10],
            ['to_fixed', 0, 10, 0],
            ['to_fixed', 10, 0, 0],
            ['to_fixed', 0, 0, 0],
            ['to_percent', 100, 100, 100],
            ['to_percent', 10, 100, 10],
            ['to_percent', 10, 70, 7],
            ['to_percent', 100, 10, 10],
            ['by_fixed', 100, 100, 0],
            ['by_fixed', 10, 100, 90],
            ['by_fixed', 100, 10, 0],
            ['by_percent', 100, 100, 0],
            ['by_percent', 100, 10, 0],
            ['by_percent', 100, 1, 0],
            ['by_percent', 10, 100, 90],
            ['by_percent', 10, 10, 9],
            ['by_percent', 1, 10, 9.90],
        ];
    }
}
