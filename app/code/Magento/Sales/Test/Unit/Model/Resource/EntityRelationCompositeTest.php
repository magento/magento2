<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Resource;

/**
 * Class EntityRelationCompositeTest
 */
class EntityRelationCompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\EntityRelationComposite
     */
    protected $entityRelationComposite;

    /**
     * @var \Magento\Sales\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesModelMock;

    /**
     * @var \Magento\Sales\Model\Resource\EntityRelationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationProcessorMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    public function setUp()
    {
        $this->salesModelMock = $this->getMockBuilder('Magento\Sales\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getEventPrefix'
                ]
            )
            ->getMockForAbstractClass();
        $this->relationProcessorMock = $this->getMockBuilder('Magento\Sales\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->relationProcessorMock = $this->getMockBuilder('Magento\Sales\Model\Resource\EntityRelationInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->entityRelationComposite = new \Magento\Sales\Model\Resource\EntityRelationComposite(
            $this->eventManagerMock,
            [
                'default' => $this->relationProcessorMock
            ]
        );
    }

    public function testProcessRelations()
    {
        $this->relationProcessorMock->expects($this->once())
            ->method('processRelation')
            ->with($this->salesModelMock);
        $this->salesModelMock->expects($this->once())
            ->method('getEventPrefix')
            ->willReturn('sales_event_prefix');
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'sales_event_prefix_process_relation',
                [
                    'object' => $this->salesModelMock
                ]
            );
        $this->entityRelationComposite->processRelations($this->salesModelMock);
    }
}
