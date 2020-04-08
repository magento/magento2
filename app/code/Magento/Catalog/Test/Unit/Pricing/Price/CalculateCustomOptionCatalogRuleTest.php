<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifier\Composite as PriceModifier;
use Magento\Catalog\Pricing\Price\CalculateCustomOptionCatalogRule;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for CalculateCustomOptionCatalogRule class.
 */
class CalculateCustomOptionCatalogRuleTest extends TestCase
{
    /**
     * @var Product|MockObject
     */
    private $saleableItemMock;

    /**
     * @var RegularPrice|MockObject
     */
    private $regularPriceMock;

    /**
     * @var SpecialPrice|MockObject
     */
    private $specialPriceMock;

    /**
     * @var CatalogRulePrice|MockObject
     */
    private $catalogRulePriceMock;

    /**
     * @var PriceModifier|MockObject
     */
    private $priceModifierMock;

    /**
     * @var CalculateCustomOptionCatalogRule
     */
    private $calculateCustomOptionCatalogRule;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->regularPriceMock = $this->createMock(RegularPrice::class);
        $this->specialPriceMock = $this->createMock(SpecialPrice::class);
        $this->catalogRulePriceMock = $this->createMock(CatalogRulePrice::class);
        $priceInfoMock = $this->createMock(Base::class);
        $this->saleableItemMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);
        $this->regularPriceMock->expects($this->any())
            ->method('getPriceCode')
            ->willReturn(RegularPrice::PRICE_CODE);
        $this->specialPriceMock->expects($this->any())
            ->method('getPriceCode')
            ->willReturn(SpecialPrice::PRICE_CODE);
        $this->catalogRulePriceMock->expects($this->any())
            ->method('getPriceCode')
            ->willReturn(CatalogRulePrice::PRICE_CODE);
        $priceInfoMock->expects($this->any())
            ->method('getPrices')
            ->willReturn(
                [
                    'regular_price' => $this->regularPriceMock,
                    'special_price' => $this->specialPriceMock,
                    'catalog_rule_price' => $this->catalogRulePriceMock
                ]
            );
        $priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->willReturnMap(
                [
                    ['regular_price', $this->regularPriceMock],
                    ['special_price', $this->specialPriceMock],
                    ['catalog_rule_price', $this->catalogRulePriceMock],
                ]
            );
        $priceCurrencyMock = $this->createMock(PriceCurrency::class);
        $priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->willReturnArgument(0);
        $this->priceModifierMock = $this->createMock(PriceModifier::class);

        $this->calculateCustomOptionCatalogRule = $objectManager->getObject(
            CalculateCustomOptionCatalogRule::class,
            [
                'priceCurrency' => $priceCurrencyMock,
                'priceModifier' => $this->priceModifierMock,
            ]
        );
    }

    /**
     * Tests correct option price calculation with different catalog rules and special prices combination.
     *
     * @dataProvider executeDataProvider
     * @param array $prices
     * @param float $catalogRulePriceModifier
     * @param float $optionPriceValue
     * @param bool $isPercent
     * @param float $expectedResult
     */
    public function testExecute(
        array $prices,
        float $catalogRulePriceModifier,
        float $optionPriceValue,
        bool $isPercent,
        float $expectedResult
    ) {
        $this->regularPriceMock->expects($this->any())
            ->method('getValue')
            ->willReturn($prices['regularPriceValue']);
        $this->specialPriceMock->expects($this->any())
            ->method('getValue')
            ->willReturn($prices['specialPriceValue']);
        $this->priceModifierMock->expects($this->any())
            ->method('modifyPrice')
            ->willReturnCallback(
                function ($price) use ($catalogRulePriceModifier) {
                    return $price * $catalogRulePriceModifier;
                }
            );

        $finalPrice = $this->calculateCustomOptionCatalogRule->execute(
            $this->saleableItemMock,
            $optionPriceValue,
            $isPercent
        );

        $this->assertSame($expectedResult, $finalPrice);
    }

    /**
     * Data provider for testExecute.
     *
     * "Active" means this price type has biggest discount, so other prices doesn't count.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function executeDataProvider(): array
    {
        return [
            'No special price, no catalog price rules, fixed option price' => [
                'prices' => [
                    'regularPriceValue' => 1000,
                    'specialPriceValue' => 1000,
                ],
                'catalogRulePriceModifier' => 1.0,
                'optionPriceValue' => 100.0,
                'isPercent' => false,
                'expectedResult' => 100.0
            ],
            'No special price, no catalog price rules, percent option price' => [
                'prices' => [
                    'regularPriceValue' => 1000,
                    'specialPriceValue' => 1000,
                ],
                'catalogRulePriceModifier' => 1.0,
                'optionPriceValue' => 100.0,
                'isPercent' => true,
                'expectedResult' => 1000.0
            ],
            'No special price, catalog price rule set, fixed option price' => [
                'prices' => [
                    'regularPriceValue' => 1000,
                    'specialPriceValue' => 1000,
                ],
                'catalogRulePriceModifier' => 0.9,
                'optionPriceValue' => 100.0,
                'isPercent' => false,
                'expectedResult' => 90.0
            ],
            'No special price, catalog price rule set, percent option price' => [
                'prices' => [
                    'regularPriceValue' => 1000,
                    'specialPriceValue' => 1000,
                ],
                'catalogRulePriceModifier' => 0.9,
                'optionPriceValue' => 100.0,
                'isPercent' => true,
                'expectedResult' => 900.0
            ],
            'Special price set, no catalog price rule, fixed option price' => [
                'prices' => [
                    'regularPriceValue' => 1000,
                    'specialPriceValue' => 900,
                ],
                'catalogRulePriceModifier' => 1.0,
                'optionPriceValue' => 100.0,
                'isPercent' => false,
                'expectedResult' => 100.0
            ],
            'Special price set, no catalog price rule, percent option price' => [
                'prices' => [
                    'regularPriceValue' => 1000,
                    'specialPriceValue' => 900,
                ],
                'catalogRulePriceModifier' => 1.0,
                'optionPriceValue' => 100.0,
                'isPercent' => true,
                'expectedResult' => 900.0
            ],
            'Special price set and active, catalog price rule set, fixed option price' => [
                'prices' => [
                    'regularPriceValue' => 1000,
                    'specialPriceValue' => 800,
                ],
                'catalogRulePriceModifier' => 0.9,
                'optionPriceValue' => 100.0,
                'isPercent' => false,
                'expectedResult' => 100.0
            ],
            'Special price set and active, catalog price rule set, percent option price' => [
                'prices' => [
                    'regularPriceValue' => 1000,
                    'specialPriceValue' => 800,
                ],
                'catalogRulePriceModifier' => 0.9,
                'optionPriceValue' => 100.0,
                'isPercent' => true,
                'expectedResult' => 800.0
            ],
            'Special price set, catalog price rule set and active, fixed option price' => [
                'prices' => [
                    'regularPriceValue' => 1000,
                    'specialPriceValue' => 950,
                ],
                'catalogRulePriceModifier' => 0.9,
                'optionPriceValue' => 100.0,
                'isPercent' => false,
                'expectedResult' => 90.0
            ],
            'Special price set, catalog price rule set and active, percent option price' => [
                'prices' => [
                    'regularPriceValue' => 1000,
                    'specialPriceValue' => 950,
                ],
                'catalogRulePriceModifier' => 0.9,
                'optionPriceValue' => 100.0,
                'isPercent' => true,
                'expectedResult' => 900.0
            ],
        ];
    }
}
