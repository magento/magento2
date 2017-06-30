<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Observer;

use Magento\Downloadable\Observer\SaveDownloadableOrderItemObserver;
use Magento\Downloadable\Model\Product\Type as DownloadableProductType;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveDownloadableOrderItemObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order */
    private $orderMock;

    /** @var SaveDownloadableOrderItemObserver */
    private $saveDownloadableOrderItemObserver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config
     */
    private $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    private $purchasedFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Downloadable\Model\Link\Purchased\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | CollectionFactory
     */
    private $itemsFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\DataObject\Copy
     */
    private $objectCopyService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\DataObject
     */
    private $resultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\DataObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event
     */
    private $eventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\Observer
     */
    private $observerMock;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSetFlag', 'getValue'])
            ->getMock();

        $this->purchasedFactory = $this->getMockBuilder(\Magento\Downloadable\Model\Link\PurchasedFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFactory = $this->getMockBuilder(\Magento\Catalog\Model\ProductFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemFactory = $this->getMockBuilder(\Magento\Downloadable\Model\Link\Purchased\ItemFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemsFactory = $this->getMockBuilder(
            \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectCopyService = $this->getMockBuilder(\Magento\Framework\DataObject\Copy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsAllowed'])
            ->getMock();

        $this->storeMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getResult', 'getQuote', 'getOrder'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStoreId', 'getState', 'isCanceled', 'getAllItems'])
            ->getMock();

        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->saveDownloadableOrderItemObserver = (new ObjectManagerHelper($this))->getObject(
            \Magento\Downloadable\Observer\SaveDownloadableOrderItemObserver::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'purchasedFactory' => $this->purchasedFactory,
                'productFactory' => $this->productFactory,
                'itemFactory' => $this->itemFactory,
                'itemsFactory' => $this->itemsFactory,
                'objectCopyService' => $this->objectCopyService
            ]
        );
    }

    public function testSaveDownloadableOrderItem()
    {
        $itemId = 100;
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->atLeastOnce())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->any())
            ->method('getProductType')
            ->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(10500);

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);
        $productType = $this->getMockBuilder(\Magento\Downloadable\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $product->expects($this->once())
            ->method('setStoreId')
            ->with(10500)
            ->willReturnSelf();
        $product->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->productFactory->expects($this->once())
            ->method('create')
            ->willReturn($product);

        $linkItem = $this->createLinkItem(12, 12, true, 'pending');
        $this->itemFactory->expects($this->once())
            ->method('create')
            ->willReturn($linkItem);

        $productType->expects($this->once())
            ->method('getLinks')
            ->willReturn([123 => $linkItem]);

        $itemMock->expects($this->once())
            ->method('getProductOptionByCode')
            ->willReturn([123]);
        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn(null);

        $purchasedLink = $this->getMockBuilder(\Magento\Downloadable\Model\Link\Purchased::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'setLinkSectionTitle', 'save'])
            ->getMock();
        $purchasedLink->expects($this->once())
            ->method('load')
            ->with($itemId, 'order_item_id')
            ->willReturnSelf();
        $purchasedLink->expects($this->once())
            ->method('setLinkSectionTitle')
            ->willReturnSelf();
        $purchasedLink->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->purchasedFactory->expects($this->any())
            ->method('create')
            ->willReturn($purchasedLink);
        $event = new \Magento\Framework\DataObject(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event
            ]
        );
        $this->saveDownloadableOrderItemObserver->execute($observer);
    }

    public function testSaveDownloadableOrderItemNotDownloadableItem()
    {
        $itemId = 100;
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->any())
            ->method('getProductType')
            ->willReturn('simple');
        $itemMock->expects($this->never())
            ->method('getProduct');
        $event = new \Magento\Framework\DataObject(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event
            ]
        );
        $this->saveDownloadableOrderItemObserver->execute($observer);
    }

    public function testSaveDownloadableOrderItemNotSavedOrderItem()
    {
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn(null);
        $event = new \Magento\Framework\DataObject(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event
            ]
        );
        $this->saveDownloadableOrderItemObserver->execute($observer);
    }

    public function testSaveDownloadableOrderItemSavedPurchasedLink()
    {
        $itemId = 100;
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->any())
            ->method('getProductType')
            ->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);

        $purchasedLink = $this->getMockBuilder(\Magento\Downloadable\Model\Link\Purchased::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'setLinkSectionTitle', 'save', 'getId'])
            ->getMock();
        $purchasedLink->expects($this->once())
            ->method('load')
            ->with($itemId, 'order_item_id')
            ->willReturnSelf();
        $purchasedLink->expects($this->once())
            ->method('getId')
            ->willReturn(123);
        $this->purchasedFactory->expects($this->any())
            ->method('create')
            ->willReturn($purchasedLink);

        $event = new \Magento\Framework\DataObject(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event
            ]
        );
        $this->saveDownloadableOrderItemObserver->execute($observer);
    }

    /**
     * @param $status
     * @param $orderItemId
     * @param bool $isSaved
     * @param null|string $expectedStatus
     * @return \Magento\Downloadable\Model\Link\Purchased\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createLinkItem($status, $orderItemId, $isSaved = false, $expectedStatus = null)
    {
        $linkItem = $this->getMockBuilder(\Magento\Downloadable\Model\Link\Purchased\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getOrderItemId', 'setStatus', 'save', 'setNumberOfDownloadsBought'])
            ->getMock();
        $linkItem->expects($this->any())
            ->method('getStatus')
            ->willReturn($status);
        if ($isSaved) {
            $linkItem->expects($this->once())
                ->method('setStatus')
                ->with($this->equalTo($expectedStatus))
                ->willReturnSelf();
            $linkItem->expects($this->once())
                ->method('save')
                ->willReturnSelf();
        }

        $linkItem->expects($this->any())
            ->method('setNumberOfDownloadsBought')
            ->willReturnSelf();

        $linkItem->expects($this->any())
            ->method('getOrderItemId')
            ->willReturn($orderItemId);

        return $linkItem;
    }
}
