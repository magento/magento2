<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Helper\Data;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem;
use Magento\CatalogInventory\Model\Stock\Item as StockMock;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogInventory\Model\StockState;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option as OptionItem;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuantityValidatorTest extends TestCase
{
    /**
     * @var QuantityValidator
     */
    private $quantityValidator;

    /**
     * @var MockObject
     */
    private $stockRegistryMock;

    /**
     * @var MockObject
     */
    private $optionInitializer;

    /**
     * @var MockObject
     */
    private $observerMock;

    /**
     * @var MockObject
     */
    private $eventMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

    /**
     * @var MockObject
     */
    private $storeMock;

    /**
     * @var MockObject
     */
    private $quoteItemMock;

    /**
     * @var MockObject
     */
    private $parentItemMock;

    /**
     * @var MockObject
     */
    private $productMock;

    /**
     * @var MockObject
     */
    private $stockItemMock;

    /**
     * @var MockObject
     */
    private $parentStockItemMock;

    /**
     * @var MockObject
     */
    private $typeInstanceMock;

    /**
     * @var MockObject
     */
    private $resultMock;

    /**
     * @var MockObject
     */
    private $stockState;

    /**
     * @var MockObject
     */
    private $stockItemInitializer;

    /**
     * @var MockObject|StockStatusInterface
     */
    private $stockStatusMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->stockRegistryMock = $this->createMock(StockRegistry::class);

        $this->stockStatusMock = $this->createMock(Status::class);

        $this->optionInitializer = $this->createMock(Option::class);
        $this->stockItemInitializer = $this->createMock(StockItem::class);
        $this->stockState = $this->createMock(StockState::class);
        $this->quantityValidator = $objectManagerHelper->getObject(
            QuantityValidator::class,
            [
                'optionInitializer' => $this->optionInitializer,
                'stockItemInitializer' => $this->stockItemInitializer,
                'stockRegistry' => $this->stockRegistryMock,
                'stockState' => $this->stockState
            ]
        );
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getHasError', 'getIsSuperMode', 'getQuote'])
            ->onlyMethods(['getItemsCollection', 'removeErrorInfosByParams', 'addErrorInfo'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->createMock(Store::class);
        $this->quoteItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getProductId', 'getHasError'])
            ->onlyMethods(
                [
                    'getQuote',
                    'getQty',
                    'getProduct',
                    'getParentItem',
                    'addErrorInfo',
                    'setData',
                    'getQtyOptions',
                    'getItemId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->parentItemMock = $this->createPartialMock(Item::class, ['getProduct', 'getId', 'getStore']);
        $this->productMock = $this->createMock(Product::class);
        $this->stockItemMock = $this->createMock(StockMock::class);
        $this->parentStockItemMock = $this->getMockBuilder(StockMock::class)
            ->addMethods(['getStockStatus'])
            ->onlyMethods(['getIsInStock'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeInstanceMock = $this->createMock(Type::class);

        $this->resultMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['checkQtyIncrements', 'getMessage', 'getQuoteMessage', 'getHasError', 'getQuoteMessageIndex'])
            ->disableOriginalConstructor()
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

        $this->stockRegistryMock->expects($this->atLeastOnce())
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);

        $this->stockStatusMock
            ->method('getStockStatus')
            ->willReturn(0);

        $this->quoteItemMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'cataloginventory',
                Data::ERROR_QTY,
                __('This product is out of stock.')
            );
        $this->quoteMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'stock',
                'cataloginventory',
                Data::ERROR_QTY,
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
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);

        $this->quoteItemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($this->parentItemMock);

        $this->stockRegistryMock->expects($this->at(2))
            ->method('getStockStatus')
            ->willReturn($this->parentStockItemMock);

        $this->parentStockItemMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(0);

        $this->stockStatusMock->expects($this->atLeastOnce())
            ->method('getStockStatus')
            ->willReturn(1);

        $this->quoteItemMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'cataloginventory',
                Data::ERROR_QTY,
                __('This product is out of stock.')
            );
        $this->quoteMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'stock',
                'cataloginventory',
                Data::ERROR_QTY,
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
        $optionMock = $this->getMockBuilder(OptionItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHasError', 'getStockStateResult', 'getProduct'])
            ->getMock();
        $optionMock->expects($this->once())
            ->method('getStockStateResult')
            ->willReturn($this->resultMock);
        $optionMock->method('getProduct')
            ->willReturn($this->productMock);
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->stockRegistryMock->expects($this->at(1))
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $options = [$optionMock];
        $this->createInitialStub(1);
        $this->setUpStubForQuantity(1, true);
        $this->setUpStubForRemoveError();
        $this->parentStockItemMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn(1);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(1);
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
    public function testValidateWithOptionsAndError()
    {
        $optionMock = $this->getMockBuilder(OptionItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHasError', 'getStockStateResult', 'getProduct'])
            ->getMock();
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->stockRegistryMock->expects($this->at(1))
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $optionMock->expects($this->once())
            ->method('getStockStateResult')
            ->willReturn($this->resultMock);
        $optionMock->method('getProduct')
            ->willReturn($this->productMock);
        $options = [$optionMock];
        $this->createInitialStub(1);
        $this->setUpStubForQuantity(1, true);
        $this->setUpStubForRemoveError();
        $this->parentStockItemMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn(1);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(1);
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
     * This tests the scenario with options but has errors and remove errors from quote.
     *
     * @return void
     */
    public function testValidateAndRemoveErrorsFromQuote()
    {
        $optionMock = $this->getMockBuilder(OptionItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHasError', 'getStockStateResult', 'getProduct'])
            ->getMock();
        $quoteItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItemId', 'getErrorInfos'])
            ->getMock();
        $optionMock->expects($this->once())
            ->method('getStockStateResult')
            ->willReturn($this->resultMock);
        $optionMock->method('getProduct')
            ->willReturn($this->productMock);
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->stockRegistryMock->expects($this->at(1))
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $options = [$optionMock];
        $this->createInitialStub(1);
        $this->setUpStubForQuantity(1, true);
        $this->parentStockItemMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn(1);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(1);
        $this->quoteItemMock->expects($this->any())
            ->method('getQtyOptions')
            ->willReturn($options);
        $this->optionInitializer->expects($this->any())
            ->method('initialize')
            ->willReturn($this->resultMock);
        $optionMock->expects($this->never())
            ->method('setHasError');
        $this->quoteMock->expects($this->atLeastOnce())->method('getHasError')->willReturn(true);
        $this->quoteMock->expects($this->atLeastOnce())->method('getItemsCollection')->willReturn([$quoteItem]);
        $quoteItem->expects($this->atLeastOnce())->method('getItemId')->willReturn(4);
        $quoteItem->expects($this->atLeastOnce())->method('getErrorInfos')->willReturn([['code' => 2]]);
        $this->quoteItemMock->expects($this->atLeastOnce())->method('getItemId')->willReturn(3);
        $this->quoteMock->expects($this->atLeastOnce())->method('removeErrorInfosByParams')
            ->with(null, ['origin' => 'cataloginventory', 'code' => 1])
            ->willReturnSelf();
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
        $this->stockRegistryMock->expects($this->at(1))
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $this->quoteItemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($this->parentItemMock);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(1);
        $this->quoteItemMock->expects($this->never())
            ->method('addErrorInfo');
        $this->quoteMock->expects($this->never())
            ->method('addErrorInfo');
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This test the scenario when stock Item is not of correct type and throws appropriate exception
     *
     * @throws LocalizedException
     * @return void
     */
    public function testException()
    {
        $this->createInitialStub(1);
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn(null);
        $this->expectException(LocalizedException::class);
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * @param $qty
     * @param $hasError
     */
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

    /**
     * @param $qty
     */
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

    private function setUpStubForRemoveError()
    {
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
