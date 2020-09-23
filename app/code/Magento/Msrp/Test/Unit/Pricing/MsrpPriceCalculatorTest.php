<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Msrp\Test\Unit\Pricing;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use Magento\Msrp\Pricing\MsrpPriceCalculator;
use Magento\MsrpGroupedProduct\Pricing\MsrpPriceCalculator as MsrpGroupedCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MsrpPriceCalculatorTest extends TestCase
{
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
     * @param array $msrpPriceCalculators
     * @param Product $productMock
     * @param float $expected
     * @dataProvider getMsrpPriceValueDataProvider
     */
    public function testGetMsrpPriceValue($msrpPriceCalculatorPrice, $productMock, $expected)
    {
        $this->msrpGroupedCalculatorMock->expects($this->any())
            ->method('getMsrpPriceValue')->willReturn($msrpPriceCalculatorPrice);

        $this->assertEquals($expected, $this->pricing->getMsrpPriceValue($productMock));
    }

    /**
     * Data Provider for test getMrspPriceValue()
     *
     * @return array
     */
    public function getMsrpPriceValueDataProvider()
    {
        return [
            'Get Mrsp Price with product and msrp calculator and the same product type' => [
                23.50,
                $this->createProductMock(GroupedType::TYPE_CODE, 0),
                23.50
            ],
            'Get Mrsp Price with product and msrp calculator and the different product type' => [
                24.88,
                $this->createProductMock(ProductType::TYPE_SIMPLE, 24.88),
                24.88
            ]
        ];
    }

    /**
     * Create Product Mock
     *
     * @param string $typeId
     * @param float $msrp
     * @return MockObject
     */
    private function createProductMock($typeId, $msrp)
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getMsrp'])
            ->onlyMethods(['getTypeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->any())->method('getTypeId')->willReturn($typeId);
        $productMock->expects($this->any())->method('getMsrp')->willReturn($msrp);
        return $productMock;
    }
}
