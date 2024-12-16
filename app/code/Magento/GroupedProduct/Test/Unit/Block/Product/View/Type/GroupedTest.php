<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Block\Product\View\Type;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\GroupedProduct\Block\Product\View\Type\Grouped;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupedTest extends TestCase
{
    /**
     * @var Grouped
     */
    protected $groupedView;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var MockObject
     */
    protected $configuredValueMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $methodsProduct = [
            'getId',
            'setQty',
            'getTypeInstance',
            'getPreconfiguredValues',
            'getTypeId',
            '__wakeup',
        ];
        $this->productMock = $this->createPartialMock(Product::class, $methodsProduct);
        $this->typeInstanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);
        $this->productMock->expects(
            $this->any()
        )->method(
            'getTypeInstance'
        )->willReturn(
            $this->typeInstanceMock
        );
        $this->configuredValueMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getSuperGroup'])
            ->disableOriginalConstructor()
            ->getMock();
        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->groupedView = $helper->getObject(
            Grouped::class,
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
    public static function setPreconfiguredValueDataProvider()
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
