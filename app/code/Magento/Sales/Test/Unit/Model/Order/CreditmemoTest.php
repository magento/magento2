<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Model\ResourceModel\OrderFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\Collection as ItemCollection;

/**
 * Class CreditmemoTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $creditmemo;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmItemCollectionFactoryMock;

    protected function setUp()
    {
        $this->orderFactory = $this->getMock(
            \Magento\Sales\Model\OrderFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->cmItemCollectionFactoryMock = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory::class
        )->disableOriginalConstructor()
        ->setMethods(['create'])
        ->getMock();

        $arguments = [
            'context' => $this->getMock(\Magento\Framework\Model\Context::class, [], [], '', false),
            'registry' => $this->getMock(\Magento\Framework\Registry::class, [], [], '', false),
            'localeDate' => $this->getMock(
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class, [], [], '', false),
            'dateTime' => $this->getMock(\Magento\Framework\Stdlib\DateTime::class, [], [], '', false),
            'creditmemoConfig' => $this->getMock(
                \Magento\Sales\Model\Order\Creditmemo\Config::class, [], [], '', false),
            'orderFactory' => $this->orderFactory,
            'cmItemCollectionFactory' => $this->cmItemCollectionFactoryMock,
            'calculatorFactory' => $this->getMock(\Magento\Framework\Math\CalculatorFactory::class, [], [], '', false),
            'storeManager' => $this->getMock(\Magento\Store\Model\StoreManagerInterface::class, [], [], '', false),
            'commentFactory' => $this->getMock(
                \Magento\Sales\Model\Order\Creditmemo\CommentFactory::class,
                    [],
                    [],
                    '',
                    false
                ),
            'commentCollectionFactory' => $this->getMock(
                \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory::class,
                    [],
                    [],
                    '',
                    false
                ),
        ];
        $this->creditmemo = $objectManagerHelper->getObject(
            \Magento\Sales\Model\Order\Creditmemo::class,
            $arguments
        );
    }

    public function testGetOrder()
    {
        $orderId = 100000041;
        $this->creditmemo->setOrderId($orderId);
        $entityName = 'creditmemo';
        $order = $this->getMock(
            \Magento\Sales\Model\Order::class,
            ['load', 'setHistoryEntityName', '__wakeUp'],
            [],
            '',
            false
        );
        $this->creditmemo->setOrderId($orderId);
        $order->expects($this->atLeastOnce())
            ->method('setHistoryEntityName')
            ->with($entityName)
            ->will($this->returnSelf());
        $order->expects($this->atLeastOnce())
            ->method('load')
            ->with($orderId)
            ->will($this->returnValue($order));

        $this->orderFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($order));

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
        $this->creditmemo->getAllowZeroGrandTotal(true);
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
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->once())
            ->method('setCreditmemo')
            ->with($this->creditmemo);
        $items[] = $itemMock;

        /** @var ItemCollection|\PHPUnit_Framework_MockObject_MockObject $itemCollectionMock */
        $itemCollectionMock = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemCollectionMock->expects($this->once())
            ->method('setCreditmemoFilter')
            ->with($id)
            ->will($this->returnValue($items));

        $this->cmItemCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($itemCollectionMock));

        $itemsCollection = $this->creditmemo->getItemsCollection();
        $this->assertEquals($items, $itemsCollection);
    }

    public function testGetItemsCollectionWithoutId()
    {
        $items = [];
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->never())
            ->method('setCreditmemo');
        $items[] = $itemMock;

        /** @var ItemCollection|\PHPUnit_Framework_MockObject_MockObject $itemCollectionMock */
        $itemCollectionMock = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemCollectionMock->expects($this->once())
            ->method('setCreditmemoFilter')
            ->with(null)
            ->will($this->returnValue($items));

        $this->cmItemCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($itemCollectionMock));

        $itemsCollection = $this->creditmemo->getItemsCollection();
        $this->assertEquals($items, $itemsCollection);
    }
}
