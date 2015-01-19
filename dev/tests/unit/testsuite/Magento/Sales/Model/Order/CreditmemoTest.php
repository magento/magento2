<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Model\Resource\OrderFactory;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Resource\Order\Creditmemo\Item\CollectionFactory;
use Magento\Sales\Model\Resource\Order\Creditmemo\Item\Collection as ItemCollection;

/**
 * Class CreditmemoTest
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

    public function setUp()
    {
        $this->orderFactory = $this->getMock(
            '\Magento\Sales\Model\OrderFactory',
            ['create'],
            [],
            '',
            false
        );

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->cmItemCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Sales\Model\Resource\Order\Creditmemo\Item\CollectionFactory'
        )->disableOriginalConstructor()
        ->setMethods(['create'])
        ->getMock();

        $arguments = [
            'context' => $this->getMock('Magento\Framework\Model\Context', [], [], '', false),
            'registry' => $this->getMock('Magento\Framework\Registry', [], [], '', false),
            'localeDate' => $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface', [], [], '', false),
            'dateTime' => $this->getMock('Magento\Framework\Stdlib\DateTime', [], [], '', false),
            'creditmemoConfig' => $this->getMock('Magento\Sales\Model\Order\Creditmemo\Config', [], [], '', false),
            'orderFactory' => $this->orderFactory,
            'cmItemCollectionFactory' => $this->cmItemCollectionFactoryMock,
            'calculatorFactory' => $this->getMock('Magento\Framework\Math\CalculatorFactory', [], [], '', false),
            'storeManager' => $this->getMock('Magento\Store\Model\StoreManagerInterface', [], [], '', false),
            'commentFactory' => $this->getMock(
                    'Magento\Sales\Model\Order\Creditmemo\CommentFactory',
                    [],
                    [],
                    '',
                    false
                ),
            'commentCollectionFactory' => $this->getMock(
                    'Magento\Sales\Model\Resource\Order\Creditmemo\Comment\CollectionFactory',
                    [],
                    [],
                    '',
                    false
                ),
        ];
        $this->creditmemo = $objectManagerHelper->getObject(
            'Magento\Sales\Model\Order\Creditmemo',
            $arguments
        );
    }

    public function testGetOrder()
    {
        $orderId = 100000041;
        $this->creditmemo->setOrderId($orderId);
        $entityName = 'creditmemo';
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
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
        $itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Creditmemo\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->once())
            ->method('setCreditmemo')
            ->with($this->creditmemo);
        $items[] = $itemMock;

        /** @var ItemCollection|\PHPUnit_Framework_MockObject_MockObject $itemCollectionMock */
        $itemCollectionMock = $this->getMockBuilder('\Magento\Sales\Model\Resource\Order\Creditmemo\Item\Collection')
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
        $itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Creditmemo\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->never())
            ->method('setCreditmemo');
        $items[] = $itemMock;

        /** @var ItemCollection|\PHPUnit_Framework_MockObject_MockObject $itemCollectionMock */
        $itemCollectionMock = $this->getMockBuilder('\Magento\Sales\Model\Resource\Order\Creditmemo\Item\Collection')
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
