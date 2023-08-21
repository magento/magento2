<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Downloadable\Model\Link\Purchased\ItemFactory;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Downloadable\Model\Product\Type as DownloadableProductType;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Downloadable\Observer\SaveDownloadableOrderItemObserver;
use Magento\Framework\App\Config;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveDownloadableOrderItemObserverTest extends TestCase
{
    /** @var MockObject|Order */
    private $orderMock;

    /** @var SaveDownloadableOrderItemObserver */
    private $saveDownloadableOrderItemObserver;

    /**
     * @var MockObject|Config
     */
    private $scopeConfig;

    /**
     * @var MockObject|PurchasedFactory
     */
    private $purchasedFactory;

    /**
     * @var MockObject|ProductFactory
     */
    private $productFactory;

    /**
     * @var MockObject|ItemFactory
     */
    private $itemFactory;

    /**
     * @var MockObject|CollectionFactory
     */
    private $itemsFactory;

    /**
     * @var MockObject|Copy
     */
    private $objectCopyService;

    /**
     * @var MockObject|DataObject
     */
    private $resultMock;

    /**
     * @var MockObject|DataObject
     */
    private $storeMock;

    /**
     * @var MockObject|Event
     */
    private $eventMock;

    /**
     * @var MockObject|Observer
     */
    private $observerMock;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSetFlag', 'getValue'])
            ->getMock();

        $this->purchasedFactory = $this->getMockBuilder(PurchasedFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFactory = $this->getMockBuilder(ProductFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemFactory = $this->getMockBuilder(ItemFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemsFactory = $this->getMockBuilder(
            CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectCopyService = $this->getMockBuilder(Copy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsAllowed'])
            ->getMock();

        $this->storeMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getResult', 'getQuote', 'getOrder'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStoreId', 'getState', 'isCanceled', 'getAllItems'])
            ->getMock();

        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->saveDownloadableOrderItemObserver = (new ObjectManagerHelper($this))->getObject(
            SaveDownloadableOrderItemObserver::class,
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
        $itemMock = $this->getMockBuilder(Item::class)
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
        $itemMock->expects($this->any())
            ->method('getRealProductType')
            ->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(10500);

        $product = $this->getMockBuilder(Product::class)
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

        $purchasedLink = $this->getMockBuilder(Purchased::class)
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
        $event = new DataObject(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new Observer(
            [
                'event' => $event
            ]
        );
        $this->saveDownloadableOrderItemObserver->execute($observer);
    }

    public function testSaveDownloadableOrderItemNotDownloadableItem()
    {
        $itemId = 100;
        $itemMock = $this->getMockBuilder(Item::class)
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
        $event = new DataObject(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new Observer(
            [
                'event' => $event
            ]
        );
        $this->saveDownloadableOrderItemObserver->execute($observer);
    }

    public function testSaveDownloadableOrderItemNotSavedOrderItem()
    {
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn(null);
        $event = new DataObject(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new Observer(
            [
                'event' => $event
            ]
        );
        $result = $this->saveDownloadableOrderItemObserver->execute($observer);
        $this->assertEquals($this->saveDownloadableOrderItemObserver, $result);
    }

    public function testSaveDownloadableOrderItemSavedPurchasedLink()
    {
        $itemId = 100;
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->any())
            ->method('getProductType')
            ->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);
        $itemMock->expects($this->any())
            ->method('getRealProductType')
            ->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);

        $purchasedLink = $this->getMockBuilder(Purchased::class)
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

        $event = new DataObject(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new Observer(
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
     * @return \Magento\Downloadable\Model\Link\Purchased\Item|MockObject
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
                ->with($expectedStatus)
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
