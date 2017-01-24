<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleAnalytics\Test\Unit\Block;

use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\GoogleAnalytics\Block\Ga;
use Magento\GoogleAnalytics\Helper\Data;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit_Framework_TestCase;

class GaTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Ga | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $gaBlock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($objectManager->getObject(Escaper::class));

        $storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getFrontendName')
            ->willReturn('test');

        $storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $contextMock->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($storeManagerMock);

        $salesOrderCollectionMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $salesOrderCollectionMock->expects($this->once())
            ->method('create')
            ->willReturn($this->createCollectionMock());

        $googleAnalyticsDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->gaBlock = $objectManager->getObject(
            Ga::class,
            [
                'context' => $contextMock,
                'salesOrderCollection' => $salesOrderCollectionMock,
                'googleAnalyticsData' => $googleAnalyticsDataMock
            ]
        );
    }

    public function testOrderTrackingCode()
    {
        $expectedCode = "ga('require', 'ec', 'ec.js');
            ga('ec:addProduct', {
                                    'id': 'sku0',
                                    'name': 'testName0',
                                    'price': '0.00',
                                    'quantity': 1
                                });
            ga('ec:setAction', 'purchase', {
                                'id': '',
                                'affiliation': 'test',
                                'revenue': '10',
                                'tax': '2',
                                'shipping': '1'
                            });
            ga('send', 'pageview');";

        $this->gaBlock->setOrderIds([1, 2]);
        $this->assertEquals(
            $this->packString($expectedCode),
            $this->packString($this->gaBlock->getOrdersTrackingCode())
        );
    }

    /**
     * Create Order mock with $orderItemCount items
     *
     * @param int $orderItemCount
     * @return Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createOrderMock($orderItemCount = 1)
    {
        $orderItems = [];
        for ($i = 0; $i < $orderItemCount; $i++) {
            $orderItemMock = $this->getMockBuilder(OrderItemInterface::class)
                ->disableOriginalConstructor()
                ->getMock();

            $orderItemMock->expects($this->once())
                ->method('getSku')
                ->willReturn('sku' . $i);

            $orderItemMock->expects($this->once())
                ->method('getName')
                ->willReturn('testName' . $i);

            $orderItemMock->expects($this->once())
                ->method('getBasePrice')
                ->willReturn($i . '.00');
            $orderItemMock->expects($this->once())
                ->method('getQtyOrdered')
                ->willReturn($i + 1);

            $orderItems[] = $orderItemMock;
        }

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn($orderItems);
        $orderMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn(10);
        $orderMock->expects($this->once())
            ->method('getBaseTaxAmount')
            ->willReturn(2);
        $orderMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn($orderItemCount);
        return $orderMock;
    }

    /**
     * @return Collection | \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createCollectionMock()
    {
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->createOrderMock(1)]));
        return $collectionMock;
    }

    /**
     * Removes from $string whitespace characters
     *
     * @param string $string
     * @return string
     */
    protected function packString($string)
    {
        return preg_replace('/\s/', '', $string);
    }
}
