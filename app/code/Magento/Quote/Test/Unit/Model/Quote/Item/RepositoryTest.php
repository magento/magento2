<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
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

    /** @var \Magento\Catalog\Model\CustomOptions\CustomOptionProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $customOptionProcessor;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $shippingAddressMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->quoteRepositoryMock = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->itemDataFactoryMock =
            $this->getMock('Magento\Quote\Api\Data\CartItemInterfaceFactory', ['create'], [], '', false);
        $this->dataMock = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);
        $this->quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $methods = ['getId', 'getSku', 'getQty', 'setData', '__wakeUp', 'getProduct', 'addProduct'];
        $this->quoteItemMock =
            $this->getMock('Magento\Quote\Model\Quote\Item', $methods, [], '', false);
        $this->customOptionProcessor = $this->getMock(
            'Magento\Catalog\Model\CustomOptions\CustomOptionProcessor',
            [],
            [],
            '',
            false
        );
        $this->shippingAddressMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['setCollectShippingRates'],
            [],
            '',
            false
        );

        $this->repository = new \Magento\Quote\Model\Quote\Item\Repository(
            $this->quoteRepositoryMock,
            $this->productRepositoryMock,
            $this->itemDataFactoryMock,
            ['custom_options' => $this->customOptionProcessor]
        );
    }

    /**
     * @param null|string|bool|int|float $value
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of
     * @dataProvider addItemWithInvalidQtyDataProvider
     */
    public function testSaveItemWithInvalidQty($value)
    {
        $this->markTestSkipped('MAGETWO-48531');
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue($value));
        $this->repository->save($this->dataMock);
    }

    /**
     * @return array
     */
    public function addItemWithInvalidQtyDataProvider()
    {
        return [
            ['string'],
            [0],
            [''],
            [null],
            [-12],
            [false],
            [-13.1],
        ];
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please specify all the required information.
     */
    public function testSaveCouldNotAddProduct()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 13;
        $buyRequest = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $buyRequest->expects($this->once())
            ->method('setData')
            ->with('qty', '12');
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->productMock));
        $this->dataMock->expects($this->once())->method('getSku');
        $this->quoteMock
            ->expects($this->once())
            ->method('addProduct')
            ->with($this->productMock, $buyRequest)
            ->willReturn('Please specify all the required information.');
        $this->quoteMock->expects($this->never())->method('getItemById');
        $this->quoteRepositoryMock->expects($this->never())->method('save')->with($this->quoteMock);
        $this->quoteMock
            ->expects($this->never())
            ->method('getAllItems');
        $this->customOptionProcessor->expects($this->once())
            ->method('convertToBuyRequest')
            ->with($this->dataMock)
            ->willReturn($buyRequest);
        $this->repository->save($this->dataMock);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save quote
     */
    public function testSaveCouldNotSaveException()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 13;
        $buyRequest = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->productMock));
        $this->dataMock->expects($this->once())->method('getSku');
        $this->quoteMock
            ->expects($this->once())
            ->method('addProduct')
            ->with($this->productMock, $buyRequest)
            ->willReturn($this->productMock);
        $this->quoteMock->expects($this->never())->method('getItemById');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue(null));
        $exceptionMessage = 'Could not save quote';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__($exceptionMessage));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);
        $this->quoteMock
            ->expects($this->never())
            ->method('getAllItems');
        $this->customOptionProcessor->expects($this->once())
            ->method('convertToBuyRequest')
            ->with($this->dataMock)
            ->willReturn($buyRequest);
        $buyRequest->expects($this->once())
            ->method('setData')
            ->with('qty', '12');
        $this->repository->save($this->dataMock);
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 13;
        $buyRequest = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $buyRequest->expects($this->once())
            ->method('setData')
            ->with('qty', '12');
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->productMock));
        $this->dataMock->expects($this->once())->method('getSku');
        $this->quoteMock
            ->expects($this->once())
            ->method('addProduct')
            ->with($this->productMock, $buyRequest)
            ->willReturn($this->productMock);
        $this->quoteMock->expects($this->never())->method('getItemById');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue(null));
        $this->quoteMock
            ->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->quoteItemMock]);
        $this->quoteItemMock->expects($this->any())->method('getId');
        $this->customOptionProcessor->expects($this->once())
            ->method('convertToBuyRequest')
            ->with($this->dataMock)
            ->willReturn($buyRequest);
        $this->assertEquals($this->quoteItemMock, $this->repository->save($this->dataMock));
    }

    public function testSaveWithCustomOption()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 13;
        $buyRequest = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->productMock));
        $this->dataMock->expects($this->once())->method('getSku');
        $this->quoteMock->expects($this->once())
            ->method('addProduct')
            ->with($this->productMock, $buyRequest)
            ->willReturn($this->productMock);
        $this->quoteMock->expects($this->never())->method('getItemById');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue(null));
        $this->quoteMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->quoteItemMock]);
        $this->quoteItemMock->expects($this->any())->method('getId');
        $this->customOptionProcessor->expects($this->once())
            ->method('convertToBuyRequest')
            ->with($this->dataMock)
            ->willReturn($buyRequest);
        $this->assertEquals($this->quoteItemMock, $this->repository->save($this->dataMock));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart 11 doesn't contain item  5
     */
    public function testUpdateItemWithInvalidQuoteItem()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 11;
        $itemId = 5;
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue($itemId));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue(false));
        $this->quoteItemMock->expects($this->never())->method('setData');
        $this->quoteItemMock->expects($this->never())->method('addProduct');

        $this->repository->save($this->dataMock);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save quote
     */
    public function testUpdateItemWithCouldNotSaveException()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 11;
        $itemId = 5;
        $buyRequest = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue($itemId));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->any())->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->quoteItemMock->expects($this->never())->method('setData');
        $this->productRepositoryMock
            ->expects($this->never())->method('get');
        $this->quoteItemMock->expects($this->never())->method('addProduct');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteMock
            ->expects($this->never())
            ->method('getAllItems');
        $this->quoteItemMock->expects($this->never())->method('getId');
        $exceptionMessage = 'Could not save quote';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__($exceptionMessage));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);
        $this->customOptionProcessor->expects($this->once())
            ->method('convertToBuyRequest')
            ->with($this->dataMock)
            ->willReturn($buyRequest);
        $buyRequest->expects($this->once())
            ->method('setData')
            ->with('qty', '12');
        $this->repository->save($this->dataMock);
    }

    /**
     * @return void
     */
    public function testUpdateItemQty()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 11;
        $itemId = 5;
        $buyRequest = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue($itemId));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->dataMock->expects($this->once())->method('getId')->willReturn($itemId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->quoteItemMock->expects($this->never())->method('setData');
        $this->productRepositoryMock
            ->expects($this->never())->method('get');
        $this->quoteItemMock->expects($this->never())->method('addProduct');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock
            ->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->quoteItemMock]);
        $this->quoteItemMock->expects($this->any())->method('getId')->willReturn($itemId);
        $this->customOptionProcessor->expects($this->once())
            ->method('convertToBuyRequest')
            ->with($this->dataMock)
            ->willReturn($buyRequest);
        $buyRequest->expects($this->once())
            ->method('setData')
            ->with('qty', '12');
        $this->quoteMock->expects($this->once())
            ->method('updateItem')
            ->with($itemId, $buyRequest)
            ->willReturn($this->dataMock);
        $this->assertEquals($this->quoteItemMock, $this->repository->save($this->dataMock));
    }

    /**
     * @return void
     */
    public function testUpdateItemOptions()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 11;
        $itemId = 5;
        $buyRequest = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $cartItemProcessorMock = $this->getMock('\Magento\Quote\Model\Quote\Item\CartItemProcessorInterface');
        $this->repository = new \Magento\Quote\Model\Quote\Item\Repository(
            $this->quoteRepositoryMock,
            $this->productRepositoryMock,
            $this->itemDataFactoryMock,
            ['simple' => $cartItemProcessorMock, 'custom_options' => $this->customOptionProcessor]
        );
        $requestMock = $this->getMock('\Magento\Framework\DataObject', ['setQty', 'getData'], [], '', false);
        $cartItemProcessorMock->expects($this->once())->method('convertToBuyRequest')->willReturn($requestMock);
        $cartItemProcessorMock
            ->expects($this->once())
            ->method('processOptions')
            ->willReturn($this->quoteItemMock);
        $requestMock->expects($this->once())->method('setQty')->with(12)->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('getData')
            ->willReturn([]);
        $this->quoteMock
            ->expects($this->once())
            ->method('updateItem')
            ->with($itemId, $buyRequest)
            ->willReturn($this->quoteItemMock);
        $this->dataMock->expects($this->any())->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue($itemId));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productRepositoryMock
            ->expects($this->never())->method('get');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock
            ->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->quoteItemMock]);
        $this->quoteItemMock->expects($this->any())->method('getId')->willReturn($itemId);
        $this->quoteItemMock->expects($this->any())->method('getQty')->willReturn(12);
        $this->customOptionProcessor->expects($this->once())
            ->method('convertToBuyRequest')
            ->with($this->dataMock)
            ->willReturn($buyRequest);
        $this->assertEquals($this->quoteItemMock, $this->repository->save($this->dataMock));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart 11 doesn't contain item  5
     */
    public function testDeleteWithInvalidQuoteItem()
    {
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue(false));
        $this->quoteMock->expects($this->never())->method('removeItem');

        $this->repository->deleteById($cartId, $itemId);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not remove item from quote
     */
    public function testDeleteWithCouldNotSaveException()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteMock->expects($this->once())
            ->method('removeItem')->with($itemId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $exceptionMessage = 'Could not remove item from quote';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__($exceptionMessage));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);

        $this->repository->deleteById($cartId, $itemId);
    }

    /**
     * @return void
     */
    public function testGetList()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with(33)
            ->will($this->returnValue($quoteMock));
        $itemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false);
        $quoteMock->expects($this->any())->method('getAllItems')->will($this->returnValue([$itemMock]));

        $this->assertEquals([$itemMock], $this->repository->getList(33));
    }

    /**
     * @return void
     */
    public function testDeleteById()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteMock->expects($this->once())->method('removeItem');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);

        $this->assertTrue($this->repository->deleteById($cartId, $itemId));
    }
}
