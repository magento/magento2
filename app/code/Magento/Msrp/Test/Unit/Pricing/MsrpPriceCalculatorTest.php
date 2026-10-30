<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Msrp\Test\Unit\Pricing;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use Magento\Msrp\Pricing\MsrpPriceCalculator;
use Magento\MsrpGroupedProduct\Pricing\MsrpPriceCalculator as MsrpGroupedCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MsrpPriceCalculatorTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var MsrpPriceCalculator
     */
    private $pricing;

    /**
     * @var MsrpGroupedCalculator|MockObject
     */
    private $msrpGroupedCalculatorMock;

    /**
     * Prepare environment to test
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->msrpGroupedCalculatorMock = $this->createMock(MsrpGroupedCalculator::class);
        $this->pricing = $objectManager->getObject(
            MsrpPriceCalculator::class,
            [
                'msrpPriceCalculators' => [
                    [
                        'productType' => GroupedType::TYPE_CODE,
                        'priceCalculator' => $this->msrpGroupedCalculatorMock
                    ]
                ]
            ]
        );
    }

    /**
     * Test getMrspPriceValue() with the data provider below
     *
     * @param float $msrpPriceCalculatorPrice
     * @param \Closure $productMock
     * @param float $expected
     */
    #[DataProvider('getMsrpPriceValueDataProvider')]
    public function testGetMsrpPriceValue(float $msrpPriceCalculatorPrice, \Closure $productMock, float $expected): void
    {
        $productMock = $productMock($this);
        $this->msrpGroupedCalculatorMock->expects($this->any())
            ->method('getMsrpPriceValue')->willReturn($msrpPriceCalculatorPrice);

        $this->assertEquals($expected, $this->pricing->getMsrpPriceValue($productMock));
    }

    /**
     * Data Provider for test getMrspPriceValue()
     *
     * @return array
     */
    public static function getMsrpPriceValueDataProvider(): array
    {
        return [
            'Get Mrsp Price with product and msrp calculator and the same product type' => [
                23.50,
                static fn (self $testCase) => $testCase->createProductMock(GroupedType::TYPE_CODE, 0),
                23.50
            ],
            'Get Mrsp Price with product and msrp calculator and the different product type' => [
                24.88,
                static fn (self $testCase) => $testCase->createProductMock(ProductType::TYPE_SIMPLE, 24.88),
                24.88
            ]
        ];
    }

    /**
     * Create Product Mock
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @param string $typeId
     * @param float $msrp
     * @return MockObject
     */
    private function createProductMock(string $typeId, float $msrp): MockObject
    {
        $productMock = $this->createPartialMockWithReflection(
            Product::class,
            ['getMsrp', 'getTypeId']
        );
        $productMock->expects($this->any())->method('getTypeId')->willReturn($typeId);
        $productMock->expects($this->any())->method('getMsrp')->willReturn($msrp);
        return $productMock;
    }
}
