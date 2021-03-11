<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Ui\AllowedProductTypes;
use Magento\Catalog\Api\Data\ProductInterface;

class AllowedProductTypesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @return void
     */
    public function testGetAllowedProductTypesWithoutConstructorArguments()
    {
        /** @var AllowedProductTypes $testedClass */
        $testedClass = (new ObjectManagerHelper($this))->getObject(AllowedProductTypes::class);
        $this->assertSame([], $testedClass->getAllowedProductTypes());
    }

    /**
     * @return void
     */
    public function testGetAllowedProductTypes()
    {
        $productTypes = ['simple', 'virtual'];
        /** @var AllowedProductTypes $testedClass */
        $testedClass = (new ObjectManagerHelper($this))->getObject(
            AllowedProductTypes::class,
            ['productTypes' => $productTypes]
        );

        $this->assertSame($productTypes, $testedClass->getAllowedProductTypes());
    }

    /**
     * @param string $typeId
     * @param bool $expectedResult
     * @dataProvider isAllowedProductTypeDataProvider
     */
    public function testIsAllowedProductType($typeId, $expectedResult)
    {
        $productTypes = ['simple', 'virtual'];
        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($typeId);

        /** @var AllowedProductTypes $testedClass */
        $testedClass = (new ObjectManagerHelper($this))->getObject(
            AllowedProductTypes::class,
            ['productTypes' => $productTypes]
        );

        $this->assertSame($expectedResult, $testedClass->isAllowedProductType($productMock));
    }

    /**
     * @return array
     */
    public function isAllowedProductTypeDataProvider()
    {
        return [
            ['typeId' => 'simple', 'expectedResult' => true],
            ['typeId' => 'downloadable', 'expectedResult' => false],
        ];
    }
}
