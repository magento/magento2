<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Service\V1\Item;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WriteService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLoaderMock;

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

    protected function setUp()
    {
        $this->quoteRepositoryMock =
            $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->productLoaderMock =
            $this->getMock('\Magento\Catalog\Service\V1\Product\ProductLoader', [], [], '', false);
        $this->dataMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\Item', [], [], '', false);
        $this->quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->quoteItemMock =
            $this->getMock('\Magento\Sales\Model\Quote\Item', ['getId', 'setData', '__wakeUp'], [], '', false);

        $this->service = new WriteService($this->quoteRepositoryMock, $this->productLoaderMock);
    }

    /**
     * @param null|string|bool|int|float $value
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of
     * @dataProvider addItemWithInvalidQtyDataProvider
     */
    public function testAddItemWithInvalidQty($value)
    {
        $cartId = 12;
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue($value));

        $this->service->addItem($cartId, $this->dataMock);
    }

    public function addItemWithInvalidQtyDataProvider()
    {
        return array(
            array('string'),
            array(0),
            array(''),
            array(null),
            array(-12),
            array(false),
            array(-13.1),
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not add item to quote
     */
    public function testAddItemCouldNotSaveException()
    {
        $cartId = 13;
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue(12));
        $this->quoteRepositoryMock->expects($this->once())->method('get')
            ->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->dataMock->expects($this->once())->method('getSku')->will($this->returnValue('product_sku'));
        $this->productLoaderMock->expects($this->once())
            ->method('load')->with('product_sku')->will($this->returnValue($this->productMock));
        $this->quoteMock->expects($this->once())->method('addProduct')->with($this->productMock, 12);
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $exceptionMessage = 'Could not add item to quote';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException($exceptionMessage);
        $this->quoteMock->expects($this->once())->method('save')->will($this->throwException($exception));

        $this->service->addItem($cartId, $this->dataMock);
    }

    public function testAddItem()
    {
        $cartId = 13;
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue(12));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->productLoaderMock->expects($this->once())->method('load')->will($this->returnValue($this->productMock));
        $this->dataMock->expects($this->once())->method('getSku');
        $this->quoteMock->expects($this->once())->method('addProduct')->with($this->productMock, 12);
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('save');
        $this->quoteMock
            ->expects($this->once())
            ->method('getItemByProduct')
            ->with($this->productMock)
            ->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('getId')->will($this->returnValue(5));
        $this->assertEquals(5, $this->service->addItem($cartId, $this->dataMock));
    }

    /**
     * @param null|string|bool|int|float $value
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of
     * @dataProvider updateItemWithInvalidQtyDataProvider
     */
    public function testUpdateItemWithInvalidQty($value)
    {
        $cartId = 11;
        $itemID = 'item_sku';
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue($value));
        $this->service->updateItem($cartId, $itemID, $this->dataMock);
    }

    public function updateItemWithInvalidQtyDataProvider()
    {
        return array(
            array('string'),
            array(0),
            array(''),
            array(null),
            array(-12),
            array(false),
            array(-13.1),
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart 11 doesn't contain item  5
     */
    public function testUpdateItemWithInvalidQuoteItem()
    {
        $cartId = 11;
        $itemId = 5;
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue(12));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue(false));
        $this->quoteItemMock->expects($this->never())->method('setData');

        $this->service->updateItem($cartId, $itemId, $this->dataMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not update quote item
     */
    public function testUpdateItemWithCouldNotSaveException()
    {
        $cartId = 11;
        $itemId = 5;
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue(12));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('setData')->with('qty', 12);
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $exceptionMessage = 'Could not update quote item';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException($exceptionMessage);
        $this->quoteMock->expects($this->once())->method('save')->will($this->throwException($exception));

        $this->service->updateItem($cartId, $itemId, $this->dataMock);
    }

    public function testUpdateItem()
    {
        $cartId = 11;
        $itemId = 5;
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue(12));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('setData')->with('qty', 12);
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('save')->will($this->returnValue($this->quoteMock));

        $this->assertTrue($this->service->updateItem($cartId, $itemId, $this->dataMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart 11 doesn't contain item  5
     */
    public function testRemoveItemWithInvalidQuoteItem()
    {
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue(false));
        $this->quoteMock->expects($this->never())->method('removeItem');

        $this->service->removeItem($cartId, $itemId, $this->dataMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not remove item from quote
     */
    public function testRemoveItemWithCouldNotSaveException()
    {
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteMock->expects($this->once())
            ->method('removeItem')->with($itemId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $exceptionMessage = 'Could not remove item from quote';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException($exceptionMessage);
        $this->quoteMock->expects($this->once())->method('save')->will($this->throwException($exception));

        $this->service->removeItem($cartId, $itemId, $this->dataMock);
    }

    public function testRemoveItem()
    {
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteMock->expects($this->once())->method('removeItem');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('save')->will($this->returnValue($this->quoteMock));

        $this->assertTrue($this->service->removeItem($cartId, $itemId, $this->dataMock));
    }
}
