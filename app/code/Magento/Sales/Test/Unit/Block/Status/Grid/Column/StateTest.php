<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Block\Status\Grid\Column;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Sales\Block\Status\Grid\Column\State
     */
    private $stateColumn;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderStatusCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->orderStatusCollectionFactoryMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class,
            ['create'],
            [],
            '',
            false,
            false
        );
        $this->configMock = $helper->getObject(
            \Magento\Sales\Model\Order\Config::class,
            [
                'orderStatusCollectionFactory' => $this->orderStatusCollectionFactoryMock
            ]
        );
        $this->stateColumn = $helper
            ->getObject(
                \Magento\Sales\Block\Status\Grid\Column\State::class,
                [
                    'config' => $this->configMock,
                ]
            );
    }

    public function testDecorateState()
    {
        $rowMock = $this->getMock(\Magento\Sales\Model\Order\Status::class, [], [], '', false);
        $rowMock->expects($this->any())->method('getStatus')->willReturn('fraud');
        $columnMock = $this->getMock(\Magento\Backend\Block\Widget\Grid\Column::class, [], [], '', false);
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

        $result = $this->stateColumn->decorateState('processing', $rowMock, $columnMock, false);
        $this->assertSame('processing[processing]', $result);
    }
}
