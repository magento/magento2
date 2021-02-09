<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing\Validator;

use \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as AdvancedPricing;

/**
 * Test of TierPriceType.
 */
class TierPriceTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  AdvancedPricing\Validator\TierPriceType
     */
    private $tierPriceType;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->tierPriceType = $objectManager->getObject(
            AdvancedPricing\Validator\TierPriceType::class,
            []
        );
    }

    /**
     * Test for isValid() method.
     *
     * @dataProvider isValidDataProvider
     * @param array $value
     * @param bool $expectedResult
     */
    public function testIsValid(array $value, $expectedResult)
    {
        $result = $this->tierPriceType->isValid($value);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data Provider for testIsValid().
     *
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [
                [AdvancedPricing::COL_TIER_PRICE_TYPE => AdvancedPricing::TIER_PRICE_TYPE_FIXED],
                true
            ],
            [
                [AdvancedPricing::COL_TIER_PRICE_TYPE => AdvancedPricing::TIER_PRICE_TYPE_PERCENT],
                true
            ],
            [
                [],
                true
            ],
            [
                [AdvancedPricing::COL_TIER_PRICE_TYPE => null],
                true
            ],
            [
                [AdvancedPricing::COL_TIER_PRICE_TYPE => 'wrong type'],
                false
            ]
        ];
    }
}
