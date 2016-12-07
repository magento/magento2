<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

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
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderStatusCollectionFactoryMock;

    protected function setUp()
    {
        $orderStatusFactory = $this->getMock(\Magento\Sales\Model\Order\StatusFactory::class, [], [], '', false, false);
        $this->orderStatusCollectionFactoryMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class,
            ['create'],
            [],
            '',
            false,
            false
        );
        $this->salesConfig = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Sales\Model\Order\Config::class,
                [
                    'orderStatusFactory' => $orderStatusFactory,
                    'orderStatusCollectionFactory' => $this->orderStatusCollectionFactoryMock
                ]
            );
    }

    public function testGetInvisibleOnFrontStatuses()
    {
        $statuses = [
            new \Magento\Framework\DataObject(
                [
                    'status' => 'canceled',
                    'is_default' => 1,
                    'visible_on_front' => 1,
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                    'status' => 'complete',
                    'is_default' => 1,
                    'visible_on_front' => 0,
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                    'status' => 'processing',
                    'is_default' => 1,
                    'visible_on_front' => 1,
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                    'status' => 'pending_payment',
                    'is_default' => 1,
                    'visible_on_front' => 0,
                ]
            ),
        ];
        $expectedResult = ['complete', 'pending_payment'];

        $collectionMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\Collection::class,
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

    public function testGetStateLabelByStateAndStatus()
    {
        $statuses = [
            new \Magento\Framework\DataObject(
                [
                    'status' => 'fraud',
                    'state' => 'processing',
                    'label' => 'Suspected Fraud',
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                    'status' => 'processing',
                    'state' => 'processing',
                    'label' => 'Processing',
                ]
            )
        ];
        $collectionMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\Collection::class,
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
        $result = $this->salesConfig->getStateLabelByStateAndStatus('processing', 'fraud');
        $this->assertSame('Suspected Fraud', $result->getText());
    }
}
