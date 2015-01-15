<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Item;

use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Quote\ItemFactory;
use Magento\Sales\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\State;
use Magento\Framework\Object;

/**
 * Tests for Magento\Sales\Model\Service\Quote\Processor
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
            'Magento\Sales\Model\Quote\ItemFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->itemMock = $this->getMock(
            'Magento\Sales\Model\Quote\Item',
            ['getId', 'setOptions', '__wakeup', 'setProduct', 'addQty', 'setCustomPrice', 'setOriginalCustomPrice'],
            [],
            '',
            false
        );
        $this->quoteItemFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->itemMock));

        $this->storeManagerMock = $this->getMock(
            'Magento\Store\Model\StoreManager',
            ['getStore'],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', ['getId', '__wakeup'], [], '', false);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->stateMock = $this->getMock(
            'Magento\Framework\App\State',
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
            'Magento\Catalog\Model\Product',
            ['getCustomOptions', '__wakeup', 'getParentProductId'],
            [],
            '',
            false
        );
        $this->objectMock = $this->getMock(
            'Magento\Framework\Object',
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
            ->with($this->equalTo('qty'), $this->equalTo(0));


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

        $this->itemMock->expects($this->never())->method('setData');

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

        $this->itemMock->expects($this->never())->method('setData');

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

        $this->itemMock->expects($this->any())
            ->method('addQty')
            ->will($this->returnValue($qty));

        $this->itemMock->expects($this->any())
            ->method('setCustomPrice')
            ->will($this->returnValue($customPrice));

        $this->itemMock->expects($this->any())
            ->method('setOriginalCustomPrice')
            ->will($this->returnValue($customPrice));

        $this->itemMock->expects($this->any())
            ->method('addQty')
            ->will($this->returnValue($qty));


        $this->productMock->expects($this->any())
            ->method('getCartQty')
            ->will($this->returnValue($qty));

        $this->objectMock->expects($this->any())
            ->method('getCustomPrice')
            ->will($this->returnValue($customPrice));

        $this->processor->prepare($this->itemMock, $this->objectMock, $this->productMock);
    }
}
