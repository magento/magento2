<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit;

class OrchestratorPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\OrchestratorPool
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Model\Operation\WriteInterface
     */
    protected $writeOperationMock;

    /**
     * @var \Magento\Framework\Model\Operation\ReadInterface
     */
    protected $readOperationMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->writeOperationMock = $this->getMockBuilder('Magento\Framework\Model\Operation\WriteInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->readOperationMock = $this->getMockBuilder('Magento\Framework\Model\Operation\ReadInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $operations = [
            'default' => [
                'read' => 'Read_Operation',
                'write' => 'Write_Operation'
            ],
            'test_write_entity_type' =>
                [
                    'write' => 'WriteOperation',
                    'read' => 'ReadOperation'
                ],
            'test_read_entity_type' =>
                [
                    'read' => 'ReadOperation'
                ]
        ];
        $this->model = new \Magento\Framework\Model\OrchestratorPool($this->objectManagerMock, $operations);
    }

    public function testGetWriteOperationDefault()
    {
        $entityType = 'not_isset_test_entity';
        $operationName = 'write';
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Write_Operation')
            ->willReturn($this->writeOperationMock);
        $this->assertEquals($this->writeOperationMock, $this->model->getWriteOperation($entityType, $operationName));
    }

    public function testGetWriteOperation()
    {
        $entityType = 'test_write_entity_type';
        $operationName = 'write';
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('WriteOperation')
            ->willReturn($this->writeOperationMock);
        $this->assertEquals($this->writeOperationMock, $this->model->getWriteOperation($entityType, $operationName));
    }

    public function testGetReadOperationDefault()
    {
        $entityType = 'not_isset_test_entity';
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Read_Operation')
            ->willReturn($this->readOperationMock);
        $this->assertEquals($this->readOperationMock, $this->model->getReadOperation($entityType));
    }

    public function testGetReadOperation()
    {
        $entityType = 'test_read_entity_type';
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('ReadOperation')
            ->willReturn($this->readOperationMock);
        $this->assertEquals($this->readOperationMock, $this->model->getReadOperation($entityType));
    }
}
