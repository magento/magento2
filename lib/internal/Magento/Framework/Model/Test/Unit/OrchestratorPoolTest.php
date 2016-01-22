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

    public function setUp()
    {
        $writeOperationInstance = $this->getMockBuilder('Magento\Framework\Model\Operation\WriteInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $readOperationInstance = $this->getMockBuilder('Magento\Framework\Model\Operation\WriteInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $operations = [
            'default' => [
                'read' => 'Read_Operation',
                'write' => 'Write_Operation'
            ],
            'test_write_entity_type' =>
                [
                    'write' => $writeOperationInstance,
                    'read' => $readOperationInstance
                ],
            'test_read_entity_type' =>
                [
                    'read' => $readOperationInstance
                ]
        ];
        $this->model = new \Magento\Framework\Model\OrchestratorPool($operations);
    }

    public function testGetWriteOperationDefault()
    {
        $entityType = 'not_isset_test_entity';
        $operationName = 'write';

        $this->assertEquals('Write_Operation', $this->model->getWriteOperation($entityType, $operationName));
    }

    public function testGetWriteOperation()
    {
        $entityType = 'test_write_entity_type';
        $operationName = 'write';
        $this->assertInstanceOf(
            'Magento\Framework\Model\Operation\WriteInterface',
            $this->model->getWriteOperation($entityType, $operationName)
        );
    }

    public function testGetReadOperationDefault()
    {
        $entityType = 'test_read_entity_type';
        $this->assertEquals('Read_Operation', $this->model->getReadOperation($entityType));
    }

    public function testGetReadOperation()
    {
        $entityType = 'test_read_entity_type';
        $operationName = 'read';
        $this->assertInstanceOf(
            'Magento\Framework\Model\Operation\WriteInterface',
            $this->model->getWriteOperation($entityType, $operationName)
        );
    }
}
