<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\State;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Processor;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Tests for Magento\Quote\Model\Service\Quote\Processor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var ItemFactory |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteItemFactoryMock;

    /**
     * @var StoreManagerInterface |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var State |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stateMock;

    /**
     * @var Product |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var Object |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectMock;

    /**
     * @var Item |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemMock;

    /**
     * @var Store |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    protected function setUp(): void
    {
        $this->quoteItemFactoryMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\ItemFactory::class,
            ['create']
        );

        $this->itemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            [
                'getId',
                'setOptions',
                '__wakeup',
                'setProduct',
                'addQty',
                'setCustomPrice',
                'setOriginalCustomPrice',
                'setData',
                'setprice',
                'getParentItem'
            ]
        );
        $this->quoteItemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->itemMock);

        $this->storeManagerMock = $this->createPartialMock(\Magento\Store\Model\StoreManager::class, ['getStore']);
        $this->storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId', '__wakeup']);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->stateMock = $this->createMock(\Magento\Framework\App\State::class);

        $this->processor = new Processor(
            $this->quoteItemFactoryMock,
            $this->storeManagerMock,
            $this->stateMock
        );

        $this->productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getCustomOptions',
                '__wakeup',
                'getParentProductId',
                'getCartQty',
                'getStickWithinParent',
                'getFinalPrice']
        );
        $this->objectMock = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getResetCount', 'getId', 'getCustomPrice']
        );
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
        $areaCode = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
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
    public function prepareChildProductDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }
}
