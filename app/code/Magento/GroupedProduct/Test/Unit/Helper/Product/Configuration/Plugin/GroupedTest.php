<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Helper\Product\Configuration\Plugin;

class GroupedTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->groupedConfigPlugin = new \Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped();
        $this->itemMock = $this->getMock('Magento\Catalog\Model\Product\Configuration\Item\ItemInterface');
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->typeInstanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            [],
            [],
            '',
            false
        );

        $this->itemMock->expects($this->any())->method('getProduct')->will($this->returnValue($this->productMock));

        $this->productMock->expects(
            $this->any()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->typeInstanceMock)
        );

        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Helper\Product\Configuration',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @covers \Magento\GroupedProduct\Helper\Product\Configuration\Plugin\Grouped::aroundGetOptions
     */
    public function testAroundGetOptionsGroupedProductWithAssociated()
    {
        $associatedProductId = 'associatedId';
        $associatedProdName = 'associatedProductName';

        $associatedProdMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $associatedProdMock->expects($this->once())->method('getId')->will($this->returnValue($associatedProductId));

        $associatedProdMock->expects($this->once())->method('getName')->will($this->returnValue($associatedProdName));

        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue([$associatedProdMock])
        );

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
        );

        $quantityItemMock = $this->getMock(
            'Magento\Catalog\Model\Product\Configuration\Item\ItemInterface',
            ['getValue', 'getProduct', 'getOptionByCode', 'getFileDownloadParams']
        );

        $quantityItemMock->expects($this->any())->method('getValue')->will($this->returnValue(1));

        $this->itemMock->expects(
            $this->once()
        )->method(
            'getOptionByCode'
        )->with(
            'associated_product_' . $associatedProductId
        )->will(
            $this->returnValue($quantityItemMock)
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
        )->will(
            $this->returnValue(false)
        );

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
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
        )->will(
            $this->returnValue('other_product_type')
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
