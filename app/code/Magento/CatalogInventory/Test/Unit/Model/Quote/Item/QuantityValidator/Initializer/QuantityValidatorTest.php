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
use Magento\CatalogInventory\Model\Stock;
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

    /**
     * @inheritDoc
     */
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
            ->addMethods(['getProductId', 'getHasError', 'getStockStateResult'])
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
     * This tests the scenario when item is not in stock.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidateOutOfStock(): void
    {
        $this->createInitialStub(0);
        $this->stockRegistryMock
            ->method('getStockItem')
            ->willReturnOnConsecutiveCalls($this->stockItemMock);

        $this->stockRegistryMock->expects($this->atLeastOnce())
            ->method('getStockStatus')
            ->willReturnOnConsecutiveCalls($this->stockStatusMock);

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
     * This tests the scenario when item is in stock but parent is not in stock.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidateInStock(): void
    {
        $this->createInitialStub(1);

        $this->quoteItemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($this->parentItemMock);

        $this->stockRegistryMock
            ->method('getStockItem')
            ->willReturnOnConsecutiveCalls($this->stockItemMock);
        $this->stockRegistryMock
            ->method('getStockStatus')
            ->willReturnOnConsecutiveCalls($this->stockStatusMock, $this->parentStockItemMock);

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
     * This tests the scenario when item is in stock and has options.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidateWithOptions(): void
    {
        $optionMock = $this->getMockBuilder(OptionItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProduct'])
            ->addMethods(['setHasError', 'getStockStateResult'])
            ->getMock();
        $optionMock->expects($this->any())
            ->method('getStockStateResult')
            ->willReturn($this->resultMock);
        $optionMock->method('getProduct')
            ->willReturn($this->productMock);
        $this->stockRegistryMock
            ->method('getStockItem')
            ->willReturnOnConsecutiveCalls($this->stockItemMock);
        $this->stockRegistryMock
            ->method('getStockStatus')
            ->willReturnOnConsecutiveCalls($this->stockStatusMock);
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
     * This tests the scenario with options but has errors.
     *
     * @param int $quantity
     * @param int $productStatus
     * @param int $productStockStatus
     * @return void
     * @throws LocalizedException
     * @dataProvider validateWithOptionsDataProvider
     */
    public function testValidateWithOptionsAndError(int $quantity, int $productStatus, int $productStockStatus): void
    {
        $optionMock = $this->getMockBuilder(OptionItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProduct'])
            ->addMethods(['setHasError', 'getStockStateResult'])
            ->getMock();
        $this->stockRegistryMock
            ->method('getStockItem')
            ->willReturnOnConsecutiveCalls($this->stockItemMock);
        $this->stockRegistryMock
            ->method('getStockStatus')
            ->willReturnOnConsecutiveCalls($this->stockStatusMock);
        $optionMock->expects($this->any())
            ->method('getStockStateResult')
            ->willReturn($this->resultMock);
        $optionMock->method('getProduct')
            ->willReturn($this->productMock);
        $options = [$optionMock];
        $this->createInitialStub($quantity);
        $this->setUpStubForQuantity($quantity, true);
        $this->setUpStubForRemoveError();
        $this->parentStockItemMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn($productStatus);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn($productStockStatus);
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
     * @return array
     */
    public static function validateWithOptionsDataProvider(): array
    {
        return [
            'when product is enabled and in stock' =>
                [1, Product\Attribute\Source\Status::STATUS_ENABLED, Stock::STOCK_IN_STOCK],
            'when product is enabled but out of stock' =>
                [1, Product\Attribute\Source\Status::STATUS_ENABLED, Stock::STOCK_OUT_OF_STOCK],
            'when product is disabled and out of stock' =>
                [1, Product\Attribute\Source\Status::STATUS_DISABLED, Stock::STOCK_OUT_OF_STOCK],
            'when product is disabled but in stock' =>
                [1, Product\Attribute\Source\Status::STATUS_DISABLED, Stock::STOCK_IN_STOCK]
        ];
    }
    /**
     * This tests the scenario with options but has errors and remove errors from quote.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidateAndRemoveErrorsFromQuote(): void
    {
        $optionMock = $this->getMockBuilder(OptionItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProduct'])
            ->addMethods(['setHasError', 'getStockStateResult'])
            ->getMock();
        $quoteItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItemId', 'getErrorInfos'])
            ->getMock();
        $optionMock->expects($this->any())
            ->method('getStockStateResult')
            ->willReturn($this->resultMock);
        $optionMock->method('getProduct')
            ->willReturn($this->productMock);
        $this->stockRegistryMock
            ->method('getStockItem')
            ->willReturnOnConsecutiveCalls($this->stockItemMock);
        $this->stockRegistryMock
            ->method('getStockStatus')
            ->willReturnOnConsecutiveCalls($this->stockStatusMock);
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
        $this->quoteMock->expects($this->any())->method('getHasError')->willReturn(true);
        $this->quoteMock->expects($this->any())->method('getItemsCollection')->willReturn([$quoteItem]);
        $quoteItem->expects($this->any())->method('getItemId')->willReturn(4);
        $quoteItem->expects($this->any())->method('getErrorInfos')->willReturn([['code' => 2]]);
        $this->quoteItemMock->expects($this->any())->method('getItemId')->willReturn(3);
        $this->quoteMock->expects($this->any())->method('removeErrorInfosByParams')
            ->with(null, ['origin' => 'cataloginventory', 'code' => 1])
            ->willReturnSelf();
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This tests the scenario when all the items are both parent and item are in stock and any errors are cleared.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testRemoveError(): void
    {
        $this->createInitialStub(1);
        $this->setUpStubForRemoveError();
        $this->quoteItemMock->expects($this->any())
            ->method('getQtyOptions')
            ->willReturn(null);
        $this->stockRegistryMock
            ->method('getStockItem')
            ->willReturnOnConsecutiveCalls($this->stockItemMock);
        $callCount = 0;
        $this->stockRegistryMock->method('getStockStatus')
            ->willReturnCallback(function () use (&$callCount) {
                return $callCount++ === 0 ? $this->stockStatusMock : null;
            });
        $this->quoteItemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($this->parentItemMock);
        $this->stockStatusMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn(1);
        $this->quoteItemMock->expects($this->any())
            ->method('addErrorInfo');
        $this->quoteMock->expects($this->any())
            ->method('addErrorInfo');
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This test the scenario when stock Item is not of correct type and throws appropriate exception.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testException(): void
    {
        $this->createInitialStub(1);
        $this->stockRegistryMock
            ->method('getStockItem')
            ->willReturn(null);
        $this->expectException(LocalizedException::class);
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This tests the scenario when the error is in the quote item already.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidateOutStockWithAlreadyErrorInQuoteItem(): void
    {
        $this->createInitialStub(1);
        $resultMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['checkQtyIncrements', 'getMessage', 'getQuoteMessage', 'getHasError'])
            ->getMock();
        $resultMock->method('getHasError')
            ->willReturn(true);
        $this->stockRegistryMock->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->quoteItemMock->method('getParentItem')
            ->willReturn($this->parentItemMock);
        $this->quoteItemMock->method('getStockStateResult')
            ->willReturn($resultMock);
        $this->stockRegistryMock
            ->method('getStockStatus')
            ->willReturnOnConsecutiveCalls($this->stockStatusMock, $this->parentStockItemMock);
        $this->parentStockItemMock->method('getStockStatus')
            ->willReturn(0);
        $this->stockStatusMock->expects($this->atLeastOnce())
            ->method('getStockStatus')
            ->willReturn(1);
        $this->quoteItemMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                null,
                Data::ERROR_QTY,
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
     * @param $qty
     * @param $hasError
     *
     * @return void
     */
    private function setUpStubForQuantity($qty, $hasError): void
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
    private function createInitialStub($qty): void
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
        $this->quoteItemMock->expects($this->any())
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

    /**
     * @return void
     */
    private function setUpStubForRemoveError(): void
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
