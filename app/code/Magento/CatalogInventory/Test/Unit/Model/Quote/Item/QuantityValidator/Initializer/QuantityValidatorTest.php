<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer;

/**
 * Class QuantityValidatorTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuantityValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quantityValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stockRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $optionInitializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $parentItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $parentStockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $typeInstanceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->stockRegistryMock = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem'])
            ->getMock();
        $this->optionInitializer = $this->getMockBuilder(
            \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemInitializer = $this->getMockBuilder(
            \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockState = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockState::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkQtyIncrements', 'getHasError', 'getQuoteMessageIndex', 'getQuoteMessage'])
            ->getMock();
        $this->quantityValidator = $objectManagerHelper->getObject(
            \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator::class,
            [
                'optionInitializer' => $this->optionInitializer,
                'stockItemInitializer' => $this->stockItemInitializer,
                'stockRegistry' => $this->stockRegistryMock,
                'stockState' => $this->stockState
            ],
            '',
            false
        );
        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
        $this->eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItem'])
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsSuperMode', 'addErrorInfo', 'getQuote', 'getItemsCollection'])
            ->getMock();
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
            ->getMock();
        $this->quoteItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getProductId', 'getQuote', 'getQty', 'getProduct', 'getParentItem',
                    'addErrorInfo', 'setData', 'getQtyOptions']
            )
            ->getMock();
        $this->parentItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct', 'getId', 'getStore'])
            ->getMock();
        $this->productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStore', 'getTypeInstance'])
            ->getMock();
        $this->stockItemMock = $this->getMockBuilder(\Magento\CatalogInventory\Model\Stock\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsInStock'])
            ->getMock();
        $this->parentStockItemMock = $this->getMockBuilder(\Magento\CatalogInventory\Model\Stock\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsInStock'])
            ->getMock();
        $this->typeInstanceMock = $this->getMockBuilder(\Magento\CatalogInventory\Model\Stock\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareQuoteItemQty'])
            ->getMock();
        $this->resultMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkQtyIncrements', 'getMessage', 'getQuoteMessage', 'getHasError'])
            ->getMock();
    }

    /**
     * This tests the scenario when item is not in stock
     *
     * @return void
     */
    public function testValidateOutOfStock()
    {
        $this->createInitialStub(0);
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->stockItemMock->expects($this->once())
            ->method('getIsInStock')
            ->willReturn(false);
        $this->quoteItemMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'cataloginventory',
                \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                __('This product is out of stock.')
            );
        $this->quoteMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'stock',
                'cataloginventory',
                \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                __('Some of the products are out of stock.')
            );
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This tests the scenario when item is in stock but parent is not in stock
     *
     * @return void
     */
    public function testValidateInStock()
    {
        $this->createInitialStub(1);
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->stockRegistryMock->expects($this->at(1))
            ->method('getStockItem')
            ->willReturn($this->parentStockItemMock);
        $this->parentStockItemMock->expects($this->once())
            ->method('getIsInStock')
            ->willReturn(false);
        $this->quoteItemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($this->parentItemMock);
        $this->stockItemMock->expects($this->once())
            ->method('getIsInStock')
            ->willReturn(true);
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->quoteItemMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'cataloginventory',
                \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                __('This product is out of stock.')
            );
        $this->quoteMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'stock',
                'cataloginventory',
                \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                __('Some of the products are out of stock.')
            );
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This tests the scenario when item is in stock and has options
     *
     * @return void
     */
    public function testValidateWithOptions()
    {
        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHasError'])
            ->getMock();
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $options = [$optionMock];
        $this->createInitialStub(1);
        $this->setUpStubForQuantity(1, true);
        $this->setUpStubForRemoveError();
        $this->parentStockItemMock->expects($this->any())
            ->method('getIsInStock')
            ->willReturn(true);
        $this->stockItemMock->expects($this->once())
            ->method('getIsInStock')
            ->willReturn(true);
        $this->quoteItemMock->expects($this->any())
            ->method('getQtyOptions')
            ->willReturn($options);
        $this->optionInitializer->expects($this->any())
            ->method('initialize')
            ->willReturn($this->resultMock);
        $optionMock->expects($this->never())
            ->method('setHasError');
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This tests the scenario with options but has errors
     *
     * @return void
     */
    public function testValidateWithOptionsAndError(){
        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHasError'])
            ->getMock();
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $options = [$optionMock];
        $this->createInitialStub(1);
        $this->setUpStubForQuantity(1, true);
        $this->setUpStubForRemoveError();
        $this->parentStockItemMock->expects($this->any())
            ->method('getIsInStock')
            ->willReturn(true);
        $this->stockItemMock->expects($this->once())
            ->method('getIsInStock')
            ->willReturn(true);
        $this->quoteItemMock->expects($this->any())
            ->method('getQtyOptions')
            ->willReturn($options);
        $this->optionInitializer->expects($this->any())
            ->method('initialize')
            ->willReturn($this->resultMock);
        $optionMock->expects($this->never())
            ->method('setHasError');
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This tests the scenario when all the items are both parent and item are in stock and any errors are cleared
     *
     * @return void
     */
    public function testRemoveError()
    {
        $this->createInitialStub(1);
        $this->setUpStubForRemoveError();
        $this->quoteItemMock->expects($this->any())
            ->method('getQtyOptions')
            ->willReturn(null);
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->quoteItemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($this->parentItemMock);
        $this->stockItemMock->expects($this->once())
            ->method('getIsInStock')
            ->willReturn(true);
        $this->quoteItemMock->expects($this->never())
            ->method('addErrorInfo');
        $this->quoteMock->expects($this->never())
            ->method('addErrorInfo');
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This test the scenario when stock Item is not of correct type and throws appropriate exception
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function testException()
    {
        $this->createInitialStub(1);
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn(null);
        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class);
        $this->quantityValidator->validate($this->observerMock);
    }

    private function setUpStubForQuantity($qty, $hasError)
    {
        $this->productMock->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($this->typeInstanceMock);
        $this->typeInstanceMock->expects($this->any())
            ->method('prepareQuoteItemQty')
            ->willReturn($qty);
        $this->quoteItemMock->expects($this->any())
            ->method('setData');
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->stockState->expects($this->any())
            ->method('checkQtyIncrements')
            ->willReturn($this->resultMock);
        $this->resultMock->expects($this->any())
            ->method('getHasError')
            ->willReturn($hasError);
        $this->resultMock->expects($this->any())
            ->method('getMessage')
            ->willReturn('');
        $this->resultMock->expects($this->any())
            ->method('getQuoteMessage')
            ->willReturn('');
        $this->resultMock->expects($this->any())
            ->method('getQuoteMessageIndex')
            ->willReturn('');
    }

    private function createInitialStub($qty)
    {
        $this->storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->quoteMock->expects($this->any())
            ->method('getIsSuperMode')
            ->willReturn(0);
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->productMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->quoteItemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn(1);
        $this->quoteItemMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($qty);
        $this->quoteItemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->eventMock->expects($this->any())
            ->method('getItem')
            ->willReturn($this->quoteItemMock);
        $this->observerMock->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->parentItemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->parentStockItemMock->expects($this->any())
            ->method('getIsInStock')
            ->willReturn(false);
        $this->storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->quoteItemMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteItemMock->expects($this->any())
            ->method('addErrorInfo');
        $this->quoteMock->expects($this->any())
            ->method('addErrorInfo');
        $this->setUpStubForQuantity(0, false);
        $this->stockItemInitializer->expects($this->any())
            ->method('initialize')
            ->willReturn($this->resultMock);
    }

    private function setUpStubForRemoveError(){
        $quoteItems = [$this->quoteItemMock];
        $this->quoteItemMock->expects($this->any())
            ->method('getHasError')
            ->willReturn(false);
        $this->quoteMock->expects($this->any())
            ->method('getItemsCollection')
            ->willReturn($quoteItems);
        $this->quoteMock->expects($this->any())
            ->method('getHasError')
            ->willReturn(false);
    }
}
