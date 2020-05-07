<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Processor;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Magento\Quote\Model\Service\Quote\Processor.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorTest extends TestCase
{
    private const STUB_STORE_ID = 111;
    private const STUB_FINAL_PRICE = 777;
    private const STUB_CUSTOM_PRICE = 222;
    private const STUB_QTY = 333;
    private const STUB_REQUEST_ID = 444;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Object|MockObject
     */
    private $objectMock;

    /**
     * @var Item|MockObject
     */
    private $itemMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $quoteItemFactoryMock = $this->createPartialMock(ItemFactory::class, ['create']);

        $this->itemMock = $this->createPartialMock(
            Item::class,
            [
                'getId',
                'setOptions',
                '__wakeup',
                'setProduct',
                'addQty',
                'setCustomPrice',
                'setOriginalCustomPrice',
                'setData',
                'setPrice'
            ]
        );
        $quoteItemFactoryMock->method('create')
            ->willReturn($this->itemMock);

        $storeManagerMock = $this->createPartialMock(StoreManager::class, ['getStore']);
        $this->storeMock = $this->createPartialMock(Store::class, ['getId', '__wakeup']);
        $storeManagerMock->method('getStore')
            ->willReturn($this->storeMock);

        $this->stateMock = $this->createMock(State::class);

        $this->processor = new Processor($quoteItemFactoryMock, $storeManagerMock, $this->stateMock);

        $this->productMock = $this->createPartialMock(
            Product::class,
            [
                'getCustomOptions',
                '__wakeup',
                'getParentProductId',
                'getCartQty',
                'getStickWithinParent',
                'getFinalPrice']
        );
        $this->objectMock = $this->createPartialMock(
            DataObject::class,
            ['getResetCount', 'getId', 'getCustomPrice']
        );
    }

    /**
     * Test init qty modification
     *
     * @return void
     */
    public function testInitWithQtyModification(): void
    {
        $storeId = self::STUB_STORE_ID;
        $productCustomOptions = 'test_custom_options';
        $requestId = self::STUB_REQUEST_ID;
        $itemId = $requestId;

        $this->productMock->method('getCustomOptions')
            ->willReturn($productCustomOptions);
        $this->productMock->method('getStickWithinParent')
            ->willReturn(false);

        $this->itemMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($itemId);
        $this->itemMock->expects($this->atLeastOnce())
            ->method('setData')
            ->willReturnMap(
                [
                    ['store_id', $storeId],
                    ['qty', 0],
                ]
            );

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $this->objectMock->expects($this->once())
            ->method('getResetCount')
            ->willReturn(true);
        $this->objectMock->expects($this->once())
            ->method('getId')
            ->willReturn($requestId);

        $result = $this->processor->init($this->productMock, $this->objectMock);
        $this->assertNotNull($result);
    }

    /**
     * Test init without modifying
     *
     * @return void
     */
    public function testInitWithoutModification(): void
    {
        $storeId = self::STUB_STORE_ID;
        $itemId = self::STUB_REQUEST_ID;

        $this->productMock->expects($this->never())->method('getCustomOptions');
        $this->productMock->expects($this->never())->method('getStickWithinParent');

        $this->productMock->expects($this->once())
            ->method('getParentProductId')
            ->willReturn(true);

        $this->itemMock->expects($this->never())->method('setOptions');
        $this->itemMock->expects($this->never())->method('setProduct');

        $this->itemMock->expects($this->once())
            ->method('getId')
            ->willReturn($itemId);

        $this->itemMock->expects($this->once())
            ->method('setData')
            ->willReturnMap(
                [
                    ['store_id', $storeId],
                ]
            );

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $this->objectMock->expects($this->never())->method('getResetCount');
        $this->objectMock->expects($this->never())->method('getId');

        $this->processor->init($this->productMock, $this->objectMock);
    }

    /**
     * Test without modification area code adminhtml
     *
     * @return void
     */
    public function testInitWithoutModificationAdminhtmlAreaCode(): void
    {
        $areaCode = FrontNameResolver::AREA_CODE;
        $storeId = self::STUB_STORE_ID;
        $requestId = self::STUB_REQUEST_ID;
        $itemId = $requestId;

        $this->stateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);

        $this->productMock->expects($this->never())->method('getCustomOptions');
        $this->productMock->expects($this->never())->method('getStickWithinParent');

        $this->productMock->expects($this->once())
            ->method('getParentProductId')
            ->willReturn(true);

        $this->itemMock->expects($this->never())->method('setOptions');
        $this->itemMock->expects($this->never())->method('setProduct');

        $this->itemMock->expects($this->once())
            ->method('getId')
            ->willReturn($itemId);

        $this->itemMock->expects($this->once())
            ->method('setData')
            ->willReturnMap(
                [
                    ['store_id', $storeId],
                ]
            );

        $this->storeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($storeId);

        $this->objectMock->expects($this->never())->method('getResetCount');
        $this->objectMock->expects($this->never())->method('getId');

        $this->processor->init($this->productMock, $this->objectMock);
    }

    /**
     * Prepare test
     *
     * @return void
     */
    public function testPrepare(): void
    {
        $qty = self::STUB_QTY;
        $customPrice = self::STUB_CUSTOM_PRICE;
        $finalPrice = self::STUB_FINAL_PRICE;

        $this->productMock->expects($this->once())
            ->method('getCartQty')
            ->willReturn($qty);
        $this->productMock->expects($this->once())
            ->method('getFinalPrice')
            ->willReturn($finalPrice);

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);
        $this->itemMock->expects($this->never())
            ->method('setData');
        $this->itemMock->expects($this->once())
            ->method('setPrice')
            ->willReturn($this->itemMock);

        $this->objectMock->expects($this->once())
            ->method('getCustomPrice')
            ->willReturn($customPrice);
        $this->objectMock->expects($this->once())
            ->method('getResetCount')
            ->willReturn(false);

        $this->itemMock->expects($this->once())
            ->method('setCustomPrice')
            ->willReturn($customPrice);
        $this->itemMock->expects($this->once())
            ->method('setOriginalCustomPrice')
            ->willReturn($customPrice);

        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }

    /**
     * Test prepare with reset stick and count
     *
     * @return void
     */
    public function testPrepareWithResetCountAndStick(): void
    {
        $qty = self::STUB_QTY;
        $customPrice = self::STUB_CUSTOM_PRICE;
        $finalPrice = self::STUB_FINAL_PRICE;

        $this->productMock->expects($this->once())
            ->method('getCartQty')
            ->willReturn($qty);
        $this->productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->willReturn(true);
        $this->productMock->expects($this->once())
            ->method('getFinalPrice')
            ->willReturn($finalPrice);

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);
        $this->itemMock->expects($this->never())
            ->method('setData');
        $this->itemMock->expects($this->once())
            ->method('setPrice')
            ->willReturn($this->itemMock);

        $this->objectMock->expects($this->once())
            ->method('getCustomPrice')
            ->willReturn($customPrice);
        $this->objectMock->expects($this->once())
            ->method('getResetCount')
            ->willReturn(true);

        $this->itemMock->expects($this->once())
            ->method('setCustomPrice')
            ->willReturn($customPrice);
        $this->itemMock->expects($this->once())
            ->method('setOriginalCustomPrice')
            ->willReturn($customPrice);

        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }

    /**
     * Test prepare with reset count and not stick and other item
     *
     * @return void
     */
    public function testPrepareWithResetCountAndNotStickAndOtherItemId(): void
    {
        $qty = self::STUB_QTY;
        $customPrice = self::STUB_CUSTOM_PRICE;
        $itemId = 1;
        $requestItemId = 2;
        $finalPrice = self::STUB_FINAL_PRICE;

        $this->productMock->expects($this->once())
            ->method('getCartQty')
            ->willReturn($qty);
        $this->productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->willReturn(false);
        $this->productMock->expects($this->once())
            ->method('getFinalPrice')
            ->willReturn($finalPrice);

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);
        $this->itemMock->expects($this->once())
            ->method('getId')
            ->willReturn($itemId);
        $this->itemMock->expects($this->never())
            ->method('setData');
        $this->itemMock->expects($this->once())
            ->method('setPrice')
            ->willReturn($this->itemMock);

        $this->objectMock->expects($this->once())
            ->method('getCustomPrice')
            ->willReturn($customPrice);
        $this->objectMock->expects($this->once())
            ->method('getResetCount')
            ->willReturn(true);
        $this->objectMock->expects($this->once())
            ->method('getId')
            ->willReturn($requestItemId);

        $this->itemMock->expects($this->once())
            ->method('setCustomPrice')
            ->willReturn($customPrice);
        $this->itemMock->expects($this->once())
            ->method('setOriginalCustomPrice')
            ->willReturn($customPrice);

        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }

    /**
     * Test prepare with reset count and not stick and same item
     *
     * @return void
     */
    public function testPrepareWithResetCountAndNotStickAndSameItemId(): void
    {
        $qty = self::STUB_QTY;
        $customPrice = self::STUB_CUSTOM_PRICE;
        $itemId = 1;
        $requestItemId = 1;
        $finalPrice = self::STUB_FINAL_PRICE;

        $this->objectMock->expects($this->once())
            ->method('getResetCount')
            ->willReturn(true);

        $this->itemMock->expects($this->once())
            ->method('getId')
            ->willReturn($itemId);
        $this->itemMock->expects($this->once())
            ->method('setData')
            ->with(CartItemInterface::KEY_QTY, 0);

        $this->productMock->expects($this->once())
            ->method('getCartQty')
            ->willReturn($qty);
        $this->productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->willReturn(false);
        $this->productMock->expects($this->once())
            ->method('getFinalPrice')
            ->willReturn($finalPrice);

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);
        $this->itemMock->expects($this->once())
            ->method('setPrice')
            ->willReturn($this->itemMock);

        $this->objectMock->expects($this->once())
            ->method('getCustomPrice')
            ->willReturn($customPrice);
        $this->objectMock->expects($this->once())
            ->method('getId')
            ->willReturn($requestItemId);

        $this->itemMock->expects($this->once())
            ->method('setCustomPrice')
            ->willReturn($customPrice);
        $this->itemMock->expects($this->once())
            ->method('setOriginalCustomPrice')
            ->willReturn($customPrice);

        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }

    /**
     * Test method to allow a user to enter zero as custom price
     *
     * @return void
     */
    public function testPrepareWithACustomPriceOfZero(): void
    {
        $customPrice = 0.0;
        $qty = 1;

        $this->objectMock->expects($this->once())
            ->method('getResetCount')
            ->willReturn(false);

        $this->productMock
            ->method("getCartQty")
            ->willReturn($qty);

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);

        $this->objectMock
            ->expects($this->once())
            ->method("getCustomPrice")
            ->willReturn($customPrice);

        $this->itemMock->expects($this->once())
            ->method("setCustomPrice")
            ->with($customPrice);

        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }
}
