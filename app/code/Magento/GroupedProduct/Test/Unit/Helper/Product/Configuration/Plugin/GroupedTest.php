<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Helper\Product\Configuration\Plugin;

class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped
     */
    protected $groupedConfigPlugin;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->groupedConfigPlugin = new \Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped();
        $this->itemMock = $this->createMock(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->typeInstanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->itemMock->expects($this->any())->method('getProduct')->willReturn($this->productMock);

        $this->productMock->expects(
            $this->any()
        )->method(
            'getTypeInstance'
        )->willReturn(
            $this->typeInstanceMock
        );

        $this->subjectMock = $this->createMock(\Magento\Catalog\Helper\Product\Configuration::class);
    }

    /**
     * @covers \Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped::aroundGetOptions
     */
    public function testAroundGetOptionsGroupedProductWithAssociated()
    {
        $associatedProductId = 'associatedId';
        $associatedProdName = 'associatedProductName';

        $associatedProdMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $associatedProdMock->expects($this->once())->method('getId')->willReturn($associatedProductId);

        $associatedProdMock->expects($this->once())->method('getName')->willReturn($associatedProdName);

        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            [$associatedProdMock]
        );

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->willReturn(
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
        );

        $quantityItemMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class,
            ['getValue', 'getProduct', 'getOptionByCode', 'getFileDownloadParams']
        );

        $quantityItemMock->expects($this->any())->method('getValue')->willReturn(1);

        $this->itemMock->expects(
            $this->once()
        )->method(
            'getOptionByCode'
        )->with(
            'associated_product_' . $associatedProductId
        )->willReturn(
            $quantityItemMock
        );

        $returnValue = [['label' => 'productName', 'value' => 2]];
        $this->closureMock = function () use ($returnValue) {
            return $returnValue;
        };

        $result = $this->groupedConfigPlugin->aroundGetOptions(
            $this->subjectMock,
            $this->closureMock,
            $this->itemMock
        );
        $expectedResult = [
            ['label' => 'associatedProductName', 'value' => 1],
            ['label' => 'productName', 'value' => 2],
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers \Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped::aroundGetOptions
     */
    public function testAroundGetOptionsGroupedProductWithoutAssociated()
    {
        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            false
        );

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->willReturn(
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
        );

        $chainCallResult = [['label' => 'label', 'value' => 'value']];

        $this->closureMock = function () use ($chainCallResult) {
            return $chainCallResult;
        };

        $result = $this->groupedConfigPlugin->aroundGetOptions(
            $this->subjectMock,
            $this->closureMock,
            $this->itemMock
        );
        $this->assertEquals($chainCallResult, $result);
    }

    /**
     * @covers \Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped::aroundGetOptions
     */
    public function testAroundGetOptionsAnotherProductType()
    {
        $chainCallResult = ['result'];

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->willReturn(
            'other_product_type'
        );

        $this->closureMock = function () use ($chainCallResult) {
            return $chainCallResult;
        };
        $this->productMock->expects($this->never())->method('getTypeInstance');

        $result = $this->groupedConfigPlugin->aroundGetOptions(
            $this->subjectMock,
            $this->closureMock,
            $this->itemMock
        );
        $this->assertEquals($chainCallResult, $result);
    }
}
