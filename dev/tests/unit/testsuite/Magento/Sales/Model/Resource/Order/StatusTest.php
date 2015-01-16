<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order;

/**
 * Class StatusTest
 *
 * @package Magento\Sales\Model\Resource
 */
class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\Status
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $selectMock;

    public function setUp()
    {
        $this->selectMock = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);
        $this->selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->selectMock->expects($this->any())->method('where');

        $this->adapterMock = $this->getMock(
            '\Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['update', 'insertOnDuplicate'],
            [],
            '',
            false
        );
        $this->adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->selectMock));

        $this->resourceMock = $this->getMock(
            '\Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );
        $tableName = 'sales_order_status_state';
        $this->resourceMock->expects($this->at(1))
            ->method('getTableName')
            ->with($this->equalTo($tableName))
            ->will($this->returnValue($tableName));
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->will(
                $this->returnValue($this->adapterMock)
            );

        $this->configMock = $this->getMock('\Magento\Eav\Model\Config', ['getConnectionName'], [], '', false);
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Sales\Model\Resource\Order\Status',
            ['resource' => $this->resourceMock]
        );
    }

    public function testAssignState()
    {
        $state = 'processing';
        $status = 'processing';
        $isDefault = 1;
        $visibleOnFront = 1;
        $tableName = 'sales_order_status_state';
        $this->adapterMock->expects($this->once())
            ->method('update')
            ->with(
                $this->equalTo($tableName),
                $this->equalTo(['is_default' => 0]),
                $this->equalTo(['state = ?' => $state])
            );
        $this->adapterMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                $this->equalTo($tableName),
                $this->equalTo(
                    [
                        'status' => $status,
                        'state' => $state,
                        'is_default' => $isDefault,
                        'visible_on_front' => $visibleOnFront,
                    ]
                )
            );
        $this->model->assignState($status, $state, $isDefault, $visibleOnFront);
    }
}
