<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Sales\Model\Order\Config
     */
    protected $salesConfig;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Status\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderStatusCollectionFactoryMock;

    public function setUp()
    {
        $orderStatusFactory = $this->getMock('Magento\Sales\Model\Order\StatusFactory', [], [], '', false, false);
        $this->orderStatusCollectionFactoryMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\CollectionFactory',
            ['create'],
            [],
            '',
            false,
            false
        );
        $this->salesConfig = new Config($orderStatusFactory, $this->orderStatusCollectionFactoryMock);
    }

    public function testGetInvisibleOnFrontStatuses()
    {
        $statuses = [
            new \Magento\Framework\Object(
                [
                    'status' => 'canceled',
                    'is_default' => 1,
                    'visible_on_front' => 1,
                ]
            ),
            new \Magento\Framework\Object(
                [
                    'status' => 'complete',
                    'is_default' => 1,
                    'visible_on_front' => 0,
                ]
            ),
            new \Magento\Framework\Object(
                [
                    'status' => 'processing',
                    'is_default' => 1,
                    'visible_on_front' => 1,
                ]
            ),
            new \Magento\Framework\Object(
                [
                    'status' => 'pending_payment',
                    'is_default' => 1,
                    'visible_on_front' => 0,
                ]
            ),
        ];
        $expectedResult = ['complete', 'pending_payment'];

        $collectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\Collection',
            ['create', 'joinStates'],
            [],
            '',
            false,
            false
        );
        $this->orderStatusCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('joinStates')
            ->will($this->returnValue($statuses));

        $result = $this->salesConfig->getInvisibleOnFrontStatuses();
        $this->assertSame($expectedResult, $result);
    }
}
