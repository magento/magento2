<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Model\Product\LinksList;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\DataObjectHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinksListTest extends TestCase
{
    /**
     * @var LinksList
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $linkFactoryMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $productTypeMock;

    /**
     * @var MockObject
     */
    protected $selectionMock;

    /**
     * @var MockObject
     */
    protected $dataObjectHelperMock;

    protected function setUp(): void
    {
        $this->linkFactoryMock = $this->createPartialMock(
            LinkInterfaceFactory::class,
            [
                'create',
            ]
        );
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectionMock = $this->getMockBuilder(Product::class)
            ->addMethods(
                [
                    'getSelectionPriceType',
                    'getSelectionPriceValue',
                    'getIsDefault',
                    'getSelectionQty',
                    'getSelectionCanChangeQty',
                    'getSelectionId'
                ]
            )
            ->onlyMethods(['getData', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getPriceType'])
            ->onlyMethods(['getTypeInstance', 'getStoreId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productTypeMock = $this->createMock(Type::class);
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
        $this->productMock->expects($this->once())->method('getPriceType')->willReturn('price_type');
        $this->selectionMock->expects($this->once())
            ->method('getSelectionPriceType')
            ->willReturn('selection_price_type');
        $this->selectionMock->expects($this->exactly(2))->method('getSelectionPriceValue')->willReturn(12);
        $this->selectionMock->expects($this->once())->method('getData')->willReturn(['some data']);
        $this->selectionMock->expects($this->once())->method('getSelectionId')->willReturn($selectionId);
        $this->selectionMock->expects($this->once())->method('getIsDefault')->willReturn(true);
        $this->selectionMock->expects($this->once())->method('getSelectionQty')->willReturn(66);
        $this->selectionMock->expects($this->once())->method('getSelectionCanChangeQty')->willReturn(22);
        $linkMock = $this->getMockForAbstractClass(LinkInterface::class);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($linkMock, ['some data'], LinkInterface::class)->willReturnSelf();
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
