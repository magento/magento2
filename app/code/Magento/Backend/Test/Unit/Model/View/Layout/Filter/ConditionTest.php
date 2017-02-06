<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\View\Layout\Filter;

use Magento\Backend\Model\View\Layout\ConditionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\Model\View\Layout\Filter\Condition;
use Magento\Backend\Model\View\Layout\StructureManager;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Backend\Model\View\Layout\ConditionPool;

class ConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StructureManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManagerMock;

    /**
     * @var Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * @var ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduledStructureMock;

    /**
     * @var ConditionPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionPoolMock;

    /**
     * @var ConditionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionMock;

    /**
     * @var Condition
     */
    private $filter;

    public function setUp()
    {
        $this->structureManagerMock = $this->getMockBuilder(StructureManager::class)
            ->getMock();
        $this->structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledStructureMock = $this->getMockBuilder(ScheduledStructure::class)
            ->getMock();
        $this->conditionPoolMock = $this->getMockBuilder(ConditionPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->conditionMock = $this->getMockBuilder(ConditionInterface::class)
            ->getMock();
        $objectManager = new ObjectManager($this);

        $this->filter = $objectManager->getObject(
            Condition::class,
            [
                'structureManager' => $this->structureManagerMock,
                'conditionPool' => $this->conditionPoolMock
            ]
        );
    }

    private function getStructureData()
    {
        return [
            'element_0' => [
                0 => '',
                1 => [
                    'attributes' => [
                        'name' => 'element_0',
                    ],
                ],
            ],
            'element_1' => [
                0 => '',
                1 => [
                    'attributes' => [
                        'name' => 'element_1',
                        'condition' => 'TestCondition1',
                    ],
                ],
            ],
            'element_2' => [
                0 => '',
                1 => [
                    'attributes' => [
                        'name' => 'element_2',
                        'condition' => 'TestCondition2',
                    ],
                ],
            ],
            'element_3' => [
                0 => '',
                1 => [
                    'attributes' => [
                        'name' => 'element_3',
                        'acl' => 'acl_non_authorised',
                    ],
                ],
            ],
        ];
    }

    public function testFilterElement()
    {
        $this->scheduledStructureMock->expects($this->once())
            ->method('getElements')
            ->willReturn($this->getStructureData());
        $this->conditionPoolMock->expects($this->exactly(2))
            ->method('getCondition')
            ->willReturnMap(
                [
                    ['TestCondition1', $this->conditionMock],
                    ['TestCondition2', $this->conditionMock]
                ]
            );
        $this->conditionMock->expects($this->at(0))
            ->method('validate')
            ->willReturn(false);
        $this->conditionMock->expects($this->at(1))
            ->method('validate')
            ->willReturn(true);
        $this->structureManagerMock->expects($this->once())
            ->method('removeElement')
            ->with($this->scheduledStructureMock, $this->structureMock, 'element_1')
            ->willReturn(true);
        $this->assertTrue($this->filter->filterElement($this->scheduledStructureMock, $this->structureMock));
    }
}
