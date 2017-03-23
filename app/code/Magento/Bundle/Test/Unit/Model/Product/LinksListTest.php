<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Model\Product;

use \Magento\Bundle\Model\Product\LinksList;

class LinksListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LinksList
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    protected function setUp()
    {
        $this->linkFactoryMock = $this->getMock(
            \Magento\Bundle\Api\Data\LinkInterfaceFactory::class,
            [
                'create',
            ],
            [],
            '',
            false
        );
        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectionMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getSelectionPriceType',
                'getSelectionPriceValue',
                'getData',
                'getIsDefault',
                'getSelectionQty',
                'getSelectionCanChangeQty',
                'getSelectionId',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getTypeInstance',
                'getStoreId',
                'getPriceType',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->productTypeMock = $this->getMock(\Magento\Bundle\Model\Product\Type::class, [], [], '', false);
        $this->model = new LinksList($this->linkFactoryMock, $this->productTypeMock, $this->dataObjectHelperMock);
    }

    public function testLinksList()
    {
        $optionId = 665;
        $selectionId = 1345;
        $this->productTypeMock->expects($this->once())
            ->method('getSelectionsCollection')
            ->with([$optionId], $this->productMock)
            ->willReturn([$this->selectionMock]);
        $this->productMock->expects($this->exactly(2))->method('getPriceType')->willReturn('price_type');
        $this->selectionMock->expects($this->once())
            ->method('getSelectionPriceType')
            ->willReturn('selection_price_type');
        $this->selectionMock->expects($this->once())->method('getSelectionPriceValue')->willReturn(12);
        $this->selectionMock->expects($this->once())->method('getData')->willReturn(['some data']);
        $this->selectionMock->expects($this->once())->method('getSelectionId')->willReturn($selectionId);
        $this->selectionMock->expects($this->once())->method('getIsDefault')->willReturn(true);
        $this->selectionMock->expects($this->once())->method('getSelectionQty')->willReturn(66);
        $this->selectionMock->expects($this->once())->method('getSelectionCanChangeQty')->willReturn(22);
        $linkMock = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($linkMock, ['some data'], \Magento\Bundle\Api\Data\LinkInterface::class)->willReturnSelf();
        $linkMock->expects($this->once())->method('setIsDefault')->with(true)->willReturnSelf();
        $linkMock->expects($this->once())->method('setQty')->with(66)->willReturnSelf();
        $linkMock->expects($this->once())->method('setCanChangeQuantity')->with(22)->willReturnSelf();
        $linkMock->expects($this->once())->method('setPrice')->with(12)->willReturnSelf();
        $linkMock->expects($this->once())->method('setId')->with($selectionId)->willReturnSelf();
        $linkMock->expects($this->once())
            ->method('setPriceType')->with('selection_price_type')->willReturnSelf();
        $this->linkFactoryMock->expects($this->once())->method('create')->willReturn($linkMock);

        $this->assertEquals([$linkMock], $this->model->getItems($this->productMock, $optionId));
    }
}
