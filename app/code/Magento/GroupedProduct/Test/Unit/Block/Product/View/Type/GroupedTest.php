<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Block\Product\View\Type;

class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GroupedProduct\Block\Product\View\Type\Grouped
     */
    protected $groupedView;

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
    protected $configuredValueMock;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $methodsProduct = [
            'getId',
            'setQty',
            'getTypeInstance',
            'getPreconfiguredValues',
            'getTypeId',
            '__wakeup',
        ];
        $this->productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, $methodsProduct);
        $this->typeInstanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);
        $this->productMock->expects(
            $this->any()
        )->method(
            'getTypeInstance'
        )->willReturn(
            $this->typeInstanceMock
        );
        $this->configuredValueMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getSuperGroup']);
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->groupedView = $helper->getObject(
            \Magento\GroupedProduct\Block\Product\View\Type\Grouped::class,
            [
                'data' => ['product' => $this->productMock],
                'layout' => $layout
            ]
        );
    }

    public function testGetAssociatedProducts()
    {
        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            'expected'
        );

        $this->assertEquals('expected', $this->groupedView->getAssociatedProducts());
    }

    /**
     * @param string $id
     * @dataProvider setPreconfiguredValueDataProvider
     */
    public function testSetPreconfiguredValue($id)
    {
        $configValue = ['id_one' => 2];
        $associatedProduct = ['key' => $this->productMock];
        $this->configuredValueMock->expects(
            $this->once()
        )->method(
            'getSuperGroup'
        )->willReturn(
            $configValue
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getPreconfiguredValues'
        )->willReturn(
            $this->configuredValueMock
        );

        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            $associatedProduct
        );

        $this->productMock->expects($this->any())->method('getId')->willReturn($id);
        $this->productMock->expects($this->any())->method('setQty')->with(2);
        $this->groupedView->setPreconfiguredValue();
    }

    /**
     * @return array
     */
    public function setPreconfiguredValueDataProvider()
    {
        return ['item_id_exist_in_config' => ['id_one'], 'item_id_not_exist_in_config' => ['id_two']];
    }

    public function testSetPreconfiguredValueIfSuperGroupNotExist()
    {
        $this->productMock->expects(
            $this->once()
        )->method(
            'getPreconfiguredValues'
        )->willReturn(
            $this->configuredValueMock
        );
        $this->configuredValueMock->expects($this->once())->method('getSuperGroup')->willReturn(false);
        $this->typeInstanceMock->expects($this->never())->method('getAssociatedProducts');
        $this->groupedView->setPreconfiguredValue();
    }
}
