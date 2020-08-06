<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as AdvancedPricing;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\TierPriceType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class TierPriceTypeTest extends TestCase
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
        $objectManager = new ObjectManager($this);
        $this->tierPriceType = $objectManager->getObject(
            TierPriceType::class,
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
