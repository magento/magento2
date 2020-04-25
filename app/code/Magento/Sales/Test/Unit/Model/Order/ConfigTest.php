<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Sales\Model\Order\Config class
 */
class ConfigTest extends TestCase
{
    /**
     * @var  Config
     */
    protected $salesConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory|MockObject
     */
    protected $orderStatusCollectionFactoryMock;

    /**
     * @var \Magento\Sales\Model\Order\StatusFactory|MockObject
     */
    protected $statusFactoryMock;

    /**
     * @var Status
     */
    protected $orderStatusModel;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->orderStatusModel = $objectManager->getObject(Status::class, [
            'storeManager' => $this->storeManagerMock,
        ]);
        $this->statusFactoryMock = $this->getMockBuilder(\Magento\Sales\Model\Order\StatusFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'create'])
            ->getMock();
        $this->orderStatusCollectionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class,
            ['create']
        );
        $this->salesConfig = $objectManager
            ->getObject(
                Config::class,
                [
                    'orderStatusFactory' => $this->statusFactoryMock,
                    'orderStatusCollectionFactory' => $this->orderStatusCollectionFactoryMock
                ]
            );
    }

    /**
     * @return void
     */
    public function testGetInvisibleOnFrontStatuses()
    {
        $statuses = [
            new DataObject(
                [
                    'status' => 'canceled',
                    'is_default' => 1,
                    'visible_on_front' => 1,
                ]
            ),
            new DataObject(
                [
                    'status' => 'complete',
                    'is_default' => 1,
                    'visible_on_front' => 0,
                ]
            ),
            new DataObject(
                [
                    'status' => 'processing',
                    'is_default' => 1,
                    'visible_on_front' => 1,
                ]
            ),
            new DataObject(
                [
                    'status' => 'pending_payment',
                    'is_default' => 1,
                    'visible_on_front' => 0,
                ]
            ),
        ];
        $expectedResult = ['complete', 'pending_payment'];

        $collectionMock = $this->createPartialMock(Collection::class, ['create', 'joinStates']);
        $this->orderStatusCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('joinStates')
            ->will($this->returnValue($statuses));

        $result = $this->salesConfig->getInvisibleOnFrontStatuses();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return void
     */
    public function testGetStateLabelByStateAndStatus()
    {
        $statuses = [
            new DataObject(
                [
                    'status' => 'fraud',
                    'state' => 'processing',
                    'label' => 'Suspected Fraud',
                ]
            ),
            new DataObject(
                [
                    'status' => 'processing',
                    'state' => 'processing',
                    'label' => 'Processing',
                ]
            )
        ];
        $collectionMock = $this->createPartialMock(Collection::class, ['create', 'joinStates']);
        $this->orderStatusCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('joinStates')
            ->will($this->returnValue($statuses));
        $result = $this->salesConfig->getStateLabelByStateAndStatus('processing', 'fraud');
        $this->assertSame('Suspected Fraud', $result->getText());
    }

    /**
     * Test get statuses
     *
     * @dataProvider getStatusesDataProvider
     *
     * @param string $state
     * @param bool $joinLabels
     * @param DataObject[] $collectionData
     * @param array $expectedResult
     */
    public function testGetStatuses($state, $joinLabels, $collectionData, $expectedResult)
    {
        $collectionMock = $this->createPartialMock(
            Collection::class,
            ['create', 'joinStates', 'addStateFilter', 'orderByLabel']
        );
        $this->orderStatusCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($collectionMock));

        $collectionMock->expects($this->once())
            ->method('addStateFilter')
            ->will($this->returnSelf());

        $collectionMock->expects($this->once())
            ->method('orderByLabel')
            ->will($this->returnValue($collectionData));

        $collectionMock->expects($this->once())
            ->method('joinStates')
            ->will($this->returnValue($collectionData));

        $this->statusFactoryMock->method('create')
            ->willReturnSelf();

        $this->statusFactoryMock->method('load')
            ->willReturn($this->orderStatusModel);

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getId')
            ->willReturn(1);

        $this->storeManagerMock->method('getStore')
            ->with($this->anything())
            ->willReturn($storeMock);

        $this->orderStatusModel->setData('store_labels', [1 => 'Pending label']);

        $result = $this->salesConfig->getStateStatuses($state, $joinLabels);
        $this->assertSame($expectedResult, $result);

        // checking data cached in private property
        $this->assertSame($result, $this->salesConfig->getStateStatuses($state, $joinLabels));
    }

    /**
     * Data provider for testGetStatuses
     *
     * @return array
     */
    public function getStatusesDataProvider()
    {
        return [
            'processing state' => [
                'state' => 'processing',
                'joinLabels' => false,
                'collectionData' => [
                    new DataObject(
                        [
                            'status' => 'fraud',
                            'state' => 'processing',
                            'store_label' => 'Suspected Fraud',
                        ]
                    ),
                    new DataObject(
                        [
                            'status' => 'processing',
                            'state' => 'processing',
                            'store_label' => 'Processing',
                        ]
                    ),
                ],
                'expectedResult' => [
                    0 => 'fraud',
                    1 => 'processing'
                ],
            ],
            'pending state' => [
                'state' => 'pending',
                'joinLabels' => true,
                'collectionData' => [
                    new DataObject(
                        [
                            'status' => 'pending_status',
                            'state' => 'pending',
                            'store_label' => 'Pending label',
                        ]
                    ),
                ],
                'expectedResult' => [
                    'pending_status' => 'Pending label'
                ],
            ],
        ];
    }
}
