<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\Order\StatusLabel;

/**
 * Test for Magento\Sales\Model\Order\Config class
 */
class ConfigTest extends TestCase
{
    /**
     * Pending status stub
     */
    const STUB_PENDING_STATUS_CODE = 'pending';

    /**
     * Store view with id 2
     */
    const STUB_STORE_VIEW_WITH_ID_2 = 2;

    /**
     * Pending label in store view 2
     */
    const STUB_STORE_VIEW_LABEL_WITH_ID_2 = 'Pending-2';

    /**
     * @var  Config
     */
    protected $salesConfig;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $orderStatusCollectionFactoryMock;

    /**
     * @var StatusFactory|MockObject
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

    protected $statusLabel;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->orderStatusModel = $objectManager->getObject(Status::class, [
            'storeManager' => $this->storeManagerMock,
        ]);
        $this->statusFactoryMock = $this->getMockBuilder(StatusFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'create'])
            ->getMock();
        $this->orderStatusCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->statusLabel = $this->createMock(StatusLabel::class);
        $this->salesConfig = $objectManager
            ->getObject(
                Config::class,
                [
                    'orderStatusFactory' => $this->statusFactoryMock,
                    'orderStatusCollectionFactory' => $this->orderStatusCollectionFactoryMock,
                    'statusLabel' => $this->statusLabel
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

        $collectionMock = $this->getMockBuilder(Collection::class)
            ->addMethods(['create'])
            ->onlyMethods(['joinStates'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderStatusCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('joinStates')
            ->willReturn($statuses);

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
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->addMethods(['create'])
            ->onlyMethods(['joinStates'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderStatusCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('joinStates')
            ->willReturn($statuses);
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
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->addMethods(['create'])
            ->onlyMethods(['joinStates', 'addStateFilter', 'orderByLabel'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderStatusCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('addStateFilter')->willReturnSelf();

        $collectionMock->expects($this->once())
            ->method('orderByLabel')
            ->willReturn($collectionData);

        $collectionMock->expects($this->once())
            ->method('joinStates')
            ->willReturn($collectionData);

        $this->statusFactoryMock->method('create')
            ->willReturnSelf();

        $this->statusFactoryMock->method('load')
            ->willReturn($this->orderStatusModel);
        $this->statusLabel->method('getStatusLabel')->willReturn('Pending label');

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
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
