<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Downloadable\Model\Link\Purchased\Item as DownloadableItem;
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
    use MockCreationTrait;
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
        $this->scopeConfig = $this->createPartialMock(Config::class, ['isSetFlag', 'getValue']);

        $this->purchasedFactory = $this->createPartialMock(PurchasedFactory::class, ['create']);

        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);

        $this->itemFactory = $this->createPartialMock(ItemFactory::class, ['create']);

        $this->itemsFactory = $this->createPartialMock(CollectionFactory::class, ['create']);

        $this->objectCopyService = $this->createMock(Copy::class);

        $this->resultMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['setIsAllowed']
        );

        $this->storeMock = $this->createMock(DataObject::class);

        $this->eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getStore', 'getResult', 'getQuote', 'getOrder']
        );

        $this->orderMock = $this->createPartialMock(
            Order::class,
            ['getId', 'getStoreId', 'getState', 'isCanceled', 'getAllItems']
        );

        $this->observerMock = $this->createPartialMock(Observer::class, ['getEvent']);

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
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->atLeastOnce())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $itemMock->method('getId')->willReturn($itemId);
        $itemMock->method('getProductType')->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);
        $itemMock->method('getRealProductType')->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);

        $this->orderMock->method('getStoreId')
            ->willReturn(10500);

        $product = $this->createMock(Product::class);
        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);
        $productType = $this->createMock(DownloadableProductType::class);
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

        // Use parent Purchased class - setLinkSectionTitle works via magic __call() methods
        $purchasedLink = $this->createPartialMock(
            Purchased::class,
            ['load', 'save']
        );
        $purchasedLink->expects($this->once())
            ->method('load')
            ->with($itemId, 'order_item_id')
            ->willReturnSelf();
        $purchasedLink->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->purchasedFactory->method('create')->willReturn($purchasedLink);
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
        $itemMock = $this->createMock(Item::class);
        $itemMock->method('getId')->willReturn($itemId);
        $itemMock->method('getProductType')->willReturn('simple');
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
        $itemMock = $this->createMock(Item::class);
        $itemMock->method('getId')->willReturn(null);
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
        $itemMock = $this->createMock(Item::class);
        $itemMock->method('getId')->willReturn($itemId);
        $itemMock->method('getProductType')->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);
        $itemMock->method('getRealProductType')->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);

        // Use parent Purchased class - setLinkSectionTitle works via magic __call() methods
        $purchasedLink = $this->createPartialMock(
            Purchased::class,
            ['load', 'save', 'getId']
        );
        $purchasedLink->expects($this->once())
            ->method('load')
            ->with($itemId, 'order_item_id')
            ->willReturnSelf();
        $purchasedLink->expects($this->once())
            ->method('getId')
            ->willReturn(123);
        $this->purchasedFactory->method('create')->willReturn($purchasedLink);

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function createLinkItem($status, $orderItemId, $isSaved = false, $expectedStatus = null)
    {
        // Use parent Item class - all getters/setters work via magic __call() methods
        $linkItem = $this->createPartialMock(DownloadableItem::class, ['save']);

        // Set data directly - getters will work via magic methods
        $linkItem->setData('status', $status);
        $linkItem->setData('order_item_id', $orderItemId);

        if ($isSaved) {
            $linkItem->expects($this->once())
                ->method('save')
                ->willReturnSelf();
        }

        return $linkItem;
    }
}
