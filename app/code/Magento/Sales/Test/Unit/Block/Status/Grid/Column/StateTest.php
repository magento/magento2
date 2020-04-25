<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Status\Grid\Column;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Status\Grid\Column\State;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var  State
     */
    private $stateColumn;

    /**
     * @var MockObject
     */
    private $orderStatusCollectionFactoryMock;

    /**
     * @var MockObject
     */
    private $configMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->orderStatusCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->configMock = $helper->getObject(
            Config::class,
            [
                'orderStatusCollectionFactory' => $this->orderStatusCollectionFactoryMock
            ]
        );
        $this->stateColumn = $helper
            ->getObject(
                State::class,
                [
                    'config' => $this->configMock,
                ]
            );
    }

    public function testDecorateState()
    {
        $rowMock = $this->getMockBuilder(Status::class)
            ->addMethods(['getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $rowMock->expects($this->any())->method('getStatus')->willReturn('fraud');
        $columnMock = $this->createMock(Column::class);
        $statuses = [
            new DataObject(
                [
                    'status' => 'fraud',
                    'state' => 'processing',
                    'is_default' => '0',
                    'label' => 'Suspected Fraud',
                ]
            ),
            new DataObject(
                [
                    'status' => 'processing',
                    'state' => 'processing',
                    'is_default' => '1',
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

        $result = $this->stateColumn->decorateState('processing', $rowMock, $columnMock, false);
        $this->assertSame('processing[Processing]', $result);
    }
}
