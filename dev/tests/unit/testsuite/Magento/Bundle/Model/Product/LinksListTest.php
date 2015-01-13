<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

class LinksListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LinksList
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkBuilderMock;

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

    protected function setUp()
    {
        $this->linkBuilderMock = $this->getMock(
            'Magento\Bundle\Api\Data\LinkDataBuilder',
            [
                'populateWithArray',
                'setIsDefault',
                'setQty',
                'setIsDefined',
                'setPrice',
                'setPriceType',
                'create',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->selectionMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                'getSelectionPriceType',
                'getSelectionPriceValue',
                'getData',
                'getIsDefault',
                'getSelectionQty',
                'getSelectionCanChangeQty',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
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
        $this->productTypeMock = $this->getMock('Magento\Bundle\Model\Product\Type', [], [], '', false);
        $this->model = new LinksList($this->linkBuilderMock, $this->productTypeMock);
    }

    public function testLinksList()
    {
        $optionId = 665;
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
        $this->selectionMock->expects($this->once())->method('getIsDefault')->willReturn(true);
        $this->selectionMock->expects($this->once())->method('getSelectionQty')->willReturn(66);
        $this->selectionMock->expects($this->once())->method('getSelectionCanChangeQty')->willReturn(22);
        $this->linkBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with(['some data'])->willReturnSelf();
        $this->linkBuilderMock->expects($this->once())->method('setIsDefault')->with(true)->willReturnSelf();
        $this->linkBuilderMock->expects($this->once())->method('setQty')->with(66)->willReturnSelf();
        $this->linkBuilderMock->expects($this->once())->method('setIsDefined')->with(22)->willReturnSelf();
        $this->linkBuilderMock->expects($this->once())->method('setPrice')->with(12)->willReturnSelf();
        $this->linkBuilderMock->expects($this->once())
            ->method('setPriceType')->with('selection_price_type')->willReturnSelf();
        $this->linkBuilderMock->expects($this->once())->method('create')->willReturnSelf();

        $this->assertEquals([$this->linkBuilderMock], $this->model->getItems($this->productMock, $optionId));
    }
}
