<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Catalog\Model\Product;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\Processor;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;

/**
 * Tests for Magento\Quote\Model\Service\Quote\Processor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var ItemFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemFactoryMock;

    /**
     * @var StoreManagerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var State |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var Product |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var Object |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectMock;

    /**
     * @var Item |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var Store |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    protected function setUp()
    {
        $this->quoteItemFactoryMock = $this->getMock(
            \Magento\Quote\Model\Quote\ItemFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->itemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
            [
                'getId',
                'setOptions',
                '__wakeup',
                'setProduct',
                'addQty',
                'setCustomPrice',
                'setOriginalCustomPrice',
                'setData'
            ],
            [],
            '',
            false
        );
        $this->quoteItemFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->itemMock));

        $this->storeManagerMock = $this->getMock(
            \Magento\Store\Model\StoreManager::class,
            ['getStore'],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock(\Magento\Store\Model\Store::class, ['getId', '__wakeup'], [], '', false);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->stateMock = $this->getMock(
            \Magento\Framework\App\State::class,
            [],
            [],
            '',
            false
        );

        $this->processor = new Processor(
            $this->quoteItemFactoryMock,
            $this->storeManagerMock,
            $this->stateMock
        );

        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getCustomOptions', '__wakeup', 'getParentProductId', 'getCartQty', 'getStickWithinParent'],
            [],
            '',
            false
        );
        $this->objectMock = $this->getMock(
            \Magento\Framework\DataObject::class,
            ['getResetCount', 'getId', 'getCustomPrice'],
            [],
            '',
            false
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
            ->will($this->returnValue($productCustomOptions));
        $this->productMock->expects($this->any())
            ->method('getStickWithinParent')
            ->will($this->returnValue(false));

        $this->itemMock->expects($this->any())
            ->method('setOptions')
            ->will($this->returnValue($productCustomOptions));
        $this->itemMock->expects($this->any())
            ->method('setProduct')
            ->will($this->returnValue($this->productMock));
        $this->itemMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($itemId));
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
            ->will($this->returnValue($storeId));

        $this->objectMock->expects($this->any())
            ->method('getResetCount')
            ->will($this->returnValue(true));
        $this->objectMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($requestId));

        $this->processor->init($this->productMock, $this->objectMock);
    }

    public function testInitWithoutModification()
    {
        $storeId = 1000000000;
        $itemId = 2000000000;

        $this->productMock->expects($this->never())->method('getCustomOptions');
        $this->productMock->expects($this->never())->method('getStickWithinParent');

        $this->productMock->expects($this->any())
            ->method('getParentProductId')
            ->will($this->returnValue(true));

        $this->itemMock->expects($this->never())->method('setOptions');
        $this->itemMock->expects($this->never())->method('setProduct');

        $this->itemMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($itemId));

        $this->itemMock->expects($this->any())
            ->method('setData')
            ->willReturnMap(
                [
                    ['store_id', $storeId],
                ]
            );

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($storeId));

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
            ->will($this->returnValue($areaCode));

        $this->productMock->expects($this->never())->method('getCustomOptions');
        $this->productMock->expects($this->never())->method('getStickWithinParent');

        $this->productMock->expects($this->any())
            ->method('getParentProductId')
            ->will($this->returnValue(true));

        $this->itemMock->expects($this->never())->method('setOptions');
        $this->itemMock->expects($this->never())->method('setProduct');

        $this->itemMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($itemId));

        $this->itemMock->expects($this->any())
            ->method('setData')
            ->willReturnMap(
                [
                    ['store_id', $storeId],
                ]
            );

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($storeId));

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

        $this->productMock->expects($this->any())
            ->method('getCartQty')
            ->will($this->returnValue($qty));
        $this->productMock->expects($this->any())
            ->method('getStickWithinParent')
            ->will($this->returnValue(false));

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);
        $this->itemMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($itemId));
        $this->itemMock->expects($this->never())
            ->method('setData');

        $this->objectMock->expects($this->any())
            ->method('getCustomPrice')
            ->will($this->returnValue($customPrice));
        $this->objectMock->expects($this->any())
            ->method('getResetCount')
            ->will($this->returnValue(false));
        $this->objectMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($requestItemId));

        $this->itemMock->expects($this->once())
            ->method('setCustomPrice')
            ->will($this->returnValue($customPrice));
        $this->itemMock->expects($this->once())
            ->method('setOriginalCustomPrice')
            ->will($this->returnValue($customPrice));

        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }

    public function testPrepareWithResetCountAndStick()
    {
        $qty = 3000000000;
        $customPrice = 400000000;
        $itemId = 1;
        $requestItemId = 1;

        $this->productMock->expects($this->any())
            ->method('getCartQty')
            ->will($this->returnValue($qty));
        $this->productMock->expects($this->any())
            ->method('getStickWithinParent')
            ->will($this->returnValue(true));

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);
        $this->itemMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($itemId));
        $this->itemMock->expects($this->never())
            ->method('setData');

        $this->objectMock->expects($this->any())
            ->method('getCustomPrice')
            ->will($this->returnValue($customPrice));
        $this->objectMock->expects($this->any())
            ->method('getResetCount')
            ->will($this->returnValue(true));
        $this->objectMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($requestItemId));

        $this->itemMock->expects($this->once())
            ->method('setCustomPrice')
            ->will($this->returnValue($customPrice));
        $this->itemMock->expects($this->once())
            ->method('setOriginalCustomPrice')
            ->will($this->returnValue($customPrice));

        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }

    public function testPrepareWithResetCountAndNotStickAndOtherItemId()
    {
        $qty = 3000000000;
        $customPrice = 400000000;
        $itemId = 1;
        $requestItemId = 2;

        $this->productMock->expects($this->any())
            ->method('getCartQty')
            ->will($this->returnValue($qty));
        $this->productMock->expects($this->any())
            ->method('getStickWithinParent')
            ->will($this->returnValue(false));

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);
        $this->itemMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($itemId));
        $this->itemMock->expects($this->never())
            ->method('setData');

        $this->objectMock->expects($this->any())
            ->method('getCustomPrice')
            ->will($this->returnValue($customPrice));
        $this->objectMock->expects($this->any())
            ->method('getResetCount')
            ->will($this->returnValue(true));
        $this->objectMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($requestItemId));

        $this->itemMock->expects($this->once())
            ->method('setCustomPrice')
            ->will($this->returnValue($customPrice));
        $this->itemMock->expects($this->once())
            ->method('setOriginalCustomPrice')
            ->will($this->returnValue($customPrice));

        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }

    public function testPrepareWithResetCountAndNotStickAndSameItemId()
    {
        $qty = 3000000000;
        $customPrice = 400000000;
        $itemId = 1;
        $requestItemId = 1;

        $this->objectMock->expects($this->any())
            ->method('getResetCount')
            ->will($this->returnValue(true));

        $this->itemMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($itemId));
        $this->itemMock->expects($this->once())
            ->method('setData')
            ->with(CartItemInterface::KEY_QTY, 0);

        $this->productMock->expects($this->any())
            ->method('getCartQty')
            ->will($this->returnValue($qty));
        $this->productMock->expects($this->any())
            ->method('getStickWithinParent')
            ->will($this->returnValue(false));

        $this->itemMock->expects($this->once())
            ->method('addQty')
            ->with($qty);

        $this->objectMock->expects($this->any())
            ->method('getCustomPrice')
            ->will($this->returnValue($customPrice));
        $this->objectMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($requestItemId));

        $this->itemMock->expects($this->once())
            ->method('setCustomPrice')
            ->will($this->returnValue($customPrice));
        $this->itemMock->expects($this->once())
            ->method('setOriginalCustomPrice')
            ->will($this->returnValue($customPrice));

        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }
}
