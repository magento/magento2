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
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Magento\Quote\Model\Service\Quote\Processor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorTest extends TestCase
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var ItemFactory|MockObject
     */
    protected $quoteItemFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var State|MockObject
     */
    protected $stateMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Object|MockObject
     */
    protected $objectMock;

    /**
     * @var Item|MockObject
     */
    protected $itemMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    protected function setUp(): void
    {
        $this->quoteItemFactoryMock = $this->createPartialMock(
            ItemFactory::class,
            ['create']
        );

        $this->itemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['setOriginalCustomPrice'])
            ->onlyMethods([
                'getId',
                'setOptions',
                'setProduct',
                'addQty',
                'setCustomPrice',
                'setData',
                'setPrice',
                'getParentItem'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteItemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->itemMock);

        $this->storeManagerMock = $this->createPartialMock(StoreManager::class, ['getStore']);
        $this->storeMock = $this->createPartialMock(Store::class, ['getId', '__wakeup']);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->stateMock = $this->createMock(State::class);

        $this->processor = new Processor(
            $this->quoteItemFactoryMock,
            $this->storeManagerMock,
            $this->stateMock
        );

        $this->productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getParentProductId', 'getCartQty', 'getStickWithinParent'])
            ->onlyMethods(['getCustomOptions', '__wakeup', 'getFinalPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getResetCount', 'getId', 'getCustomPrice'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testInitWithQtyModification()
    {
        $storeId = 1000000000;
        $productCustomOptions = 'test_custom_options';
        $requestId = 20000000;
        $itemId = $requestId;

        $this->productMock->expects($this->any())
            ->method('getCustomOptions')
            ->willReturn($productCustomOptions);
        $this->productMock->expects($this->any())
            ->method('getStickWithinParent')
            ->willReturn(false);

        $this->itemMock->expects($this->any())
            ->method('setOptions')
            ->willReturn($productCustomOptions);
        $this->itemMock->expects($this->any())
            ->method('setProduct')
            ->willReturn($this->productMock);
        $this->itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $this->itemMock->expects($this->any())
            ->method('setData')
            ->willReturnMap(
                [
                    ['store_id', $storeId],
                    ['qty', 0],
                ]
            );

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->objectMock->expects($this->any())
            ->method('getResetCount')
            ->willReturn(true);
        $this->objectMock->expects($this->any())
            ->method('getId')
            ->willReturn($requestId);

        $result = $this->processor->init($this->productMock, $this->objectMock);
        $this->assertNotNull($result);
    }

    public function testInitWithoutModification()
    {
        $storeId = 1000000000;
        $itemId = 2000000000;

        $this->productMock->expects($this->never())->method('getCustomOptions');
        $this->productMock->expects($this->never())->method('getStickWithinParent');

        $this->productMock->expects($this->any())
            ->method('getParentProductId')
            ->willReturn(true);

        $this->itemMock->expects($this->never())->method('setOptions');
        $this->itemMock->expects($this->never())->method('setProduct');

        $this->itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);

        $this->itemMock->expects($this->any())
            ->method('setData')
            ->willReturnMap(
                [
                    ['store_id', $storeId],
                ]
            );

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->objectMock->expects($this->never())->method('getResetCount');
        $this->objectMock->expects($this->never())->method('getId');

        $this->processor->init($this->productMock, $this->objectMock);
    }

    public function testInitWithoutModificationAdminhtmlAreaCode()
    {
        $areaCode = FrontNameResolver::AREA_CODE;
        $storeId = 1000000000;
        $requestId = 20000000;
        $itemId = $requestId;

        $this->stateMock->expects($this->any())
            ->method('getAreaCode')
            ->willReturn($areaCode);

        $this->productMock->expects($this->never())->method('getCustomOptions');
        $this->productMock->expects($this->never())->method('getStickWithinParent');

        $this->productMock->expects($this->any())
            ->method('getParentProductId')
            ->willReturn(true);

        $this->itemMock->expects($this->never())->method('setOptions');
        $this->itemMock->expects($this->never())->method('setProduct');

        $this->itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);

        $this->itemMock->expects($this->any())
            ->method('setData')
            ->willReturnMap(
                [
                    ['store_id', $storeId],
                ]
            );

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->objectMock->expects($this->never())->method('getResetCount');
        $this->objectMock->expects($this->never())->method('getId');

        $this->processor->init($this->productMock, $this->objectMock);
    }

    public function testPrepare()
    {
        $qty = 3000000000;
        $customPrice = 400000000;
        $itemId = 1;
        $requestItemId = 1;
        $finalPrice = 1000000000;

        $this->productMock->expects($this->any())
            ->method('getCartQty')
            ->willReturn($qty);
        $this->productMock->expects($this->any())
            ->method('getStickWithinParent')
            ->willReturn(false);
        $this->productMock->expects($this->once())
            ->method('getFinalPrice')
            ->willReturn($finalPrice);

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);
        $this->itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $this->itemMock->expects($this->never())
            ->method('setData');
        $this->itemMock->expects($this->once())
            ->method('setPrice')
            ->willReturn($this->itemMock);

        $this->objectMock->expects($this->any())
            ->method('getCustomPrice')
            ->willReturn($customPrice);
        $this->objectMock->expects($this->any())
            ->method('getResetCount')
            ->willReturn(false);
        $this->objectMock->expects($this->any())
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

    public function testPrepareWithResetCountAndStick()
    {
        $qty = 3000000000;
        $customPrice = 400000000;
        $itemId = 1;
        $requestItemId = 1;
        $finalPrice = 1000000000;

        $this->productMock->expects($this->any())
            ->method('getCartQty')
            ->willReturn($qty);
        $this->productMock->expects($this->any())
            ->method('getStickWithinParent')
            ->willReturn(true);
        $this->productMock->expects($this->once())
            ->method('getFinalPrice')
            ->willReturn($finalPrice);

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);
        $this->itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $this->itemMock->expects($this->never())
            ->method('setData');
        $this->itemMock->expects($this->once())
            ->method('setPrice')
            ->willReturn($this->itemMock);

        $this->objectMock->expects($this->any())
            ->method('getCustomPrice')
            ->willReturn($customPrice);
        $this->objectMock->expects($this->any())
            ->method('getResetCount')
            ->willReturn(true);
        $this->objectMock->expects($this->any())
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

    public function testPrepareWithResetCountAndNotStickAndOtherItemId()
    {
        $qty = 3000000000;
        $customPrice = 400000000;
        $itemId = 1;
        $requestItemId = 2;
        $finalPrice = 1000000000;

        $this->productMock->expects($this->any())
            ->method('getCartQty')
            ->willReturn($qty);
        $this->productMock->expects($this->any())
            ->method('getStickWithinParent')
            ->willReturn(false);
        $this->productMock->expects($this->once())
            ->method('getFinalPrice')
            ->willReturn($finalPrice);

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);
        $this->itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $this->itemMock->expects($this->never())
            ->method('setData');
        $this->itemMock->expects($this->once())
            ->method('setPrice')
            ->willReturn($this->itemMock);

        $this->objectMock->expects($this->any())
            ->method('getCustomPrice')
            ->willReturn($customPrice);
        $this->objectMock->expects($this->any())
            ->method('getResetCount')
            ->willReturn(true);
        $this->objectMock->expects($this->any())
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

    public function testPrepareWithResetCountAndNotStickAndSameItemId()
    {
        $qty = 3000000000;
        $customPrice = 400000000;
        $itemId = 1;
        $requestItemId = 1;
        $finalPrice = 1000000000;

        $this->objectMock->expects($this->any())
            ->method('getResetCount')
            ->willReturn(true);

        $this->itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $this->itemMock->expects($this->once())
            ->method('setData')
            ->with(CartItemInterface::KEY_QTY, 0);

        $this->productMock->expects($this->any())
            ->method('getCartQty')
            ->willReturn($qty);
        $this->productMock->expects($this->any())
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

        $this->objectMock->expects($this->any())
            ->method('getCustomPrice')
            ->willReturn($customPrice);
        $this->objectMock->expects($this->any())
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
     * @param bool $isChildrenCalculated
     * @dataProvider prepareChildProductDataProvider
     */
    public function testPrepareChildProduct(bool $isChildrenCalculated): void
    {
        $finalPrice = 10;
        $this->objectMock->method('getResetCount')
            ->willReturn(false);
        $this->productMock->method('getFinalPrice')
            ->willReturn($finalPrice);
        $this->itemMock->expects($isChildrenCalculated ? $this->once() : $this->never())
            ->method('setPrice')
            ->with($finalPrice)
            ->willReturnSelf();
        $parentItem = $this->createConfiguredMock(
            \Magento\Quote\Model\Quote\Item::class,
            [
                'isChildrenCalculated' => $isChildrenCalculated
            ]
        );
        $this->itemMock->method('getParentItem')
            ->willReturn($parentItem);
        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }

    /**
     * @return array
     */
    public static function prepareChildProductDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }
}
