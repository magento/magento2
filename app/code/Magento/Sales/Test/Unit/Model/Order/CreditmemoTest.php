<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Math\CalculatorFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\CommentFactory;
use Magento\Sales\Model\Order\Creditmemo\Config;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\Collection as ItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoTest extends TestCase
{
    /**
     * @var OrderRepositoryInterface|MockObject
     */
    protected $orderRepository;

    /**
     * @var Creditmemo
     */
    protected $creditmemo;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $cmItemCollectionFactoryMock;

    protected function setUp(): void
    {
        $this->orderRepository = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->cmItemCollectionFactoryMock = $this->getMockBuilder(
            CollectionFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $arguments = [
            'context' => $this->createMock(Context::class),
            'registry' => $this->createMock(Registry::class),
            'localeDate' => $this->createMock(
                TimezoneInterface::class
            ),
            'dateTime' => $this->createMock(DateTime::class),
            'creditmemoConfig' => $this->createMock(
                Config::class
            ),
            'cmItemCollectionFactory' => $this->cmItemCollectionFactoryMock,
            'calculatorFactory' => $this->createMock(CalculatorFactory::class),
            'storeManager' => $this->getMockForAbstractClass(StoreManagerInterface::class),
            'commentFactory' => $this->createMock(CommentFactory::class),
            'commentCollectionFactory' => $this->createMock(
                \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory::class
            ),
            'scopeConfig' => $this->scopeConfigMock,
            'orderRepository' => $this->orderRepository,
        ];
        $this->creditmemo = $objectManagerHelper->getObject(
            Creditmemo::class,
            $arguments
        );
    }

    public function testGetOrder()
    {
        $orderId = 100000041;
        $this->creditmemo->setOrderId($orderId);
        $entityName = 'creditmemo';
        $order = $this->createPartialMock(
            Order::class,
            ['load', 'setHistoryEntityName']
        );
        $this->creditmemo->setOrderId($orderId);
        $order->expects($this->atLeastOnce())
            ->method('setHistoryEntityName')
            ->with($entityName)->willReturnSelf();
        $this->orderRepository->expects($this->atLeastOnce())
            ->method('get')
            ->with($orderId)
            ->willReturn($order);

        $this->assertEquals($order, $this->creditmemo->getOrder());
    }

    public function testGetEntityType()
    {
        $this->assertEquals('creditmemo', $this->creditmemo->getEntityType());
    }

    public function testIsValidGrandTotalGrandTotalEmpty()
    {
        $this->creditmemo->setGrandTotal(0);
        $this->assertFalse($this->creditmemo->isValidGrandTotal());
    }

    public function testIsValidGrandTotalGrandTotal()
    {
        $this->creditmemo->setGrandTotal(0);
        $this->assertFalse($this->creditmemo->isValidGrandTotal());
    }

    public function testIsValidGrandTotal()
    {
        $this->creditmemo->setGrandTotal(1);
        $this->assertTrue($this->creditmemo->isValidGrandTotal());
    }

    public function testGetIncrementId()
    {
        $this->creditmemo->setIncrementId('test_increment_id');
        $this->assertEquals('test_increment_id', $this->creditmemo->getIncrementId());
    }

    public function testGetItemsCollectionWithId()
    {
        $id = 1;
        $this->creditmemo->setId($id);

        $items = [];
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->once())
            ->method('setCreditmemo')
            ->with($this->creditmemo);
        $items[] = $itemMock;

        /** @var ItemCollection|MockObject $itemCollectionMock */
        $itemCollectionMock = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $itemCollectionMock->expects($this->once())
            ->method('setCreditmemoFilter')
            ->with($id)
            ->willReturn($items);

        $this->cmItemCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemCollectionMock);

        $itemsCollection = $this->creditmemo->getItemsCollection();
        $this->assertEquals($items, $itemsCollection);
    }

    public function testGetItemsCollectionWithoutId()
    {
        $items = [];
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->never())
            ->method('setCreditmemo');
        $items[] = $itemMock;

        /** @var ItemCollection|MockObject $itemCollectionMock */
        $itemCollectionMock = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $itemCollectionMock->expects($this->once())
            ->method('setCreditmemoFilter')
            ->with(null)
            ->willReturn($items);

        $this->cmItemCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemCollectionMock);

        $itemsCollection = $this->creditmemo->getItemsCollection();
        $this->assertEquals($items, $itemsCollection);
    }

    public function testIsLastForLastCreditMemo(): void
    {
        $item = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->getMock();
        $orderItem = $this->getMockBuilder(OrderItem::class)->disableOriginalConstructor()->getMock();
        $orderItem
            ->expects($this->once())
            ->method('isDummy')
            ->willReturn(true);
        $item->expects($this->once())
            ->method('getOrderItem')
            ->willReturn($orderItem);
        $this->creditmemo->setItems([$item]);
        $this->assertTrue($this->creditmemo->isLast());
    }

    public function testIsLastForNonLastCreditMemo(): void
    {
        $item = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->getMock();
        $orderItem = $this->getMockBuilder(OrderItem::class)->disableOriginalConstructor()->getMock();
        $orderItem
            ->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $item->expects($this->once())
            ->method('getOrderItem')
            ->willReturn($orderItem);
        $item->expects($this->once())
            ->method('getOrderItem')
            ->willReturn($orderItem);
        $item->expects($this->once())
            ->method('isLast')
            ->willReturn(false);
        $this->creditmemo->setItems([$item]);
        $this->assertFalse($this->creditmemo->isLast());
    }
}
