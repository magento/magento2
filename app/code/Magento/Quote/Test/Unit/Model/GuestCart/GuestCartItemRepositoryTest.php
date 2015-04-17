<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use JsonSchema\Constraints\Object;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\GuestCart\GuestCartItemRepository;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestCartItemRepositoryTest
//    extends RepositoryTest
extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GuestCartItemRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemDataFactoryMock;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->quoteRepositoryMock =
            $this->getMock('\Magento\Quote\Model\QuoteRepository', [], [], '', false);
        $this->productRepositoryMock =
            $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface', [], [], '', false);
        $this->itemDataFactoryMock =
            $this->getMock('Magento\Quote\Api\Data\CartItemInterfaceFactory', ['create'], [], '', false);
        $this->dataMock = $this->getMock('Magento\Quote\Api\Data\CartItemInterface');
        $this->quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->quoteItemMock =
            $this->getMock('Magento\Quote\Model\Quote\Item', ['getId', 'getSku', 'setData', '__wakeUp'], [], '', false);
        $this->quoteIdMaskFactoryMock = $this->getMock('Magento\Quote\Model\QuoteIdMaskFactory', [], [], '', false);
        $this->quoteIdMaskMock = $this->getMock('Magento\Quote\Model\QuoteIdMask', [], [], '', false);

        $this->repository = new GuestCartItemRepository(
            $this->quoteRepositoryMock,
            $this->productRepositoryMock,
            $this->itemDataFactoryMock,
            $this->quoteIdMaskFactoryMock
        );
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 13;
        $this->quoteIdMaskFactoryMock->expects($this->any())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->any())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->any())
            ->method('getId')
            ->willReturn($cartId);

        $this->dataMock->expects($this->at(0))->method('getQuoteId')->willReturn($maskedCartId);
        $this->dataMock->expects($this->once())->method('setQuoteId')->with($cartId);
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->at(3))->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->productMock));
        $this->dataMock->expects($this->once())->method('getSku');
        $this->quoteMock->expects($this->once())->method('addProduct')->with($this->productMock, 12);
        $this->quoteMock->expects($this->never())->method('getItemById');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock
            ->expects($this->once())
            ->method('getItemByProduct')
            ->with($this->productMock)
            ->will($this->returnValue($this->quoteItemMock));
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue(null));
        $this->assertEquals($this->quoteItemMock, $this->repository->save($this->dataMock));
    }

    /**
     * @return void
     */
    public function testUpdateItem()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 13;
        $this->quoteIdMaskFactoryMock->expects($this->any())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->any())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->any())
            ->method('getId')
            ->willReturn($cartId);

        $itemId = 5;
        $productSku = 'product_sku';
        $this->dataMock->expects($this->at(0))->method('getQuoteId')->willReturn($maskedCartId);
        $this->dataMock->expects($this->once())->method('setQuoteId')->with($cartId);
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->at(3))->method('getQuoteId')->willReturn($cartId);
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue($itemId));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('setData')->with('qty', 12);
        $this->quoteItemMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->quoteItemMock->expects($this->never())->method('addProduct');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock
            ->expects($this->once())
            ->method('getItemByProduct')
            ->with($this->productMock)
            ->willReturn($this->quoteItemMock);
        $this->assertEquals($this->quoteItemMock, $this->repository->save($this->dataMock));
    }

    /**
     * @return void
     */
    public function testGetList()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 33;
        $this->quoteIdMaskFactoryMock->expects($this->any())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->any())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->any())
            ->method('getId')
            ->willReturn($cartId);

        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));
        $itemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false);
        $quoteMock->expects($this->any())->method('getAllItems')->will($this->returnValue([$itemMock]));

        $this->assertEquals([$itemMock], $this->repository->getList($maskedCartId));
    }

    /**
     * @return void
     */
    public function testDeleteById()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 33;
        $this->quoteIdMaskFactoryMock->expects($this->any())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->any())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->any())
            ->method('getId')
            ->willReturn($cartId);

        $itemId = 5;
        $this->itemDataFactoryMock->expects($this->once())->method('create')->willReturn($this->dataMock);
        $this->dataMock->expects($this->once())->method('setQuoteId')
            ->with($cartId)->willReturn($this->dataMock);
        $this->dataMock->expects($this->once())->method('setItemId')
            ->with($itemId)->willReturn($this->dataMock);
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->dataMock->expects($this->once())->method('getItemId')->willReturn($itemId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteMock->expects($this->once())->method('removeItem');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);

        $this->assertTrue($this->repository->deleteById($maskedCartId, $itemId));
    }
}
