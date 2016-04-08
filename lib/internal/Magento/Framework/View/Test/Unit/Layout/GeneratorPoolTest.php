<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Layout\Element
 */
namespace Magento\Framework\View\Test\Unit\Layout;

use \Magento\Framework\View\Layout\GeneratorPool;
use \Magento\Framework\View\Layout\ScheduledStructure;
use \Magento\Framework\View\Layout\Data\Structure as DataStructure;

class GeneratorPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerContextMock;

    /**
     * @var \Magento\Framework\View\Layout\Generator\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generatorContextMock;

    /**
     * @var ScheduledStructure
     */
    protected $scheduledStructure;

    /**
     * @var \Magento\Framework\View\Layout\Data\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structureMock;

    /**
     * @var GeneratorPool
     */
    protected $model;

    /**
     * @return void
     */
    protected function setUp()
    {
        // ScheduledStructure
        $this->readerContextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledStructure = new ScheduledStructure();
        $this->readerContextMock->expects($this->any())->method('getScheduledStructure')
            ->willReturn($this->scheduledStructure);

        // DataStructure
        $this->generatorContextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Generator\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureMock = $this->getMockBuilder('Magento\Framework\View\Layout\Data\Structure')
            ->disableOriginalConstructor()
            ->setMethods(['reorderChildElement'])
            ->getMock();
        $this->generatorContextMock->expects($this->any())->method('getStructure')
            ->willReturn($this->structureMock);

        $this->helperMock = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure\Helper')
            ->disableOriginalConstructor()
            ->getMock();

        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helperObjectManager->getObject(
            'Magento\Framework\View\Layout\GeneratorPool',
            [
                'helper' => $this->helperMock,
                'generators' => $this->getGeneratorsMocks()
            ]
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected function getGeneratorsMocks()
    {
        $firstGenerator = $this->getMock('Magento\Framework\View\Layout\GeneratorInterface');
        $firstGenerator->expects($this->any())->method('getType')->willReturn('first_generator');
        $firstGenerator->expects($this->atLeastOnce())->method('process');

        $secondGenerator = $this->getMock('Magento\Framework\View\Layout\GeneratorInterface');
        $secondGenerator->expects($this->any())->method('getType')->willReturn('second_generator');
        $secondGenerator->expects($this->atLeastOnce())->method('process');
        return [$firstGenerator, $secondGenerator];
    }

    /**
     * @param array $schedule
     * @param array $expectedSchedule
     * @return void
     * @dataProvider processDataProvider
     */
    public function testProcess($schedule, $expectedSchedule)
    {
        foreach ($schedule['structure'] as $structureElement) {
            $this->scheduledStructure->setStructureElement($structureElement, []);
        }

        $reorderMap = [];
        foreach ($schedule['sort'] as $elementName => $sort) {
            list($parentName, $sibling, $isAfter) = $sort;
            $this->scheduledStructure->setElementToSortList($parentName, $elementName, $sibling, $isAfter);
            $reorderMap[] = [$parentName, $elementName, $sibling, $isAfter];
        }
        foreach ($schedule['move'] as $elementName => $move) {
            $this->scheduledStructure->setElementToMove($elementName, $move);
            list($destination, $sibling, $isAfter) = $move;
            $reorderMap[] = [$destination, $elementName, $sibling, $isAfter];
        }
        $invocation = $this->structureMock->expects($this->any())->method('reorderChildElement');
        call_user_func_array([$invocation, 'withConsecutive'], $reorderMap);

        foreach ($schedule['remove'] as $remove) {
            $this->scheduledStructure->setElementToRemoveList($remove);
        }

        $this->helperMock->expects($this->atLeastOnce())->method('scheduleElement')
            ->with($this->scheduledStructure, $this->structureMock, $this->anything())
            ->willReturnCallback(function ($scheduledStructure, $structure, $elementName) use ($schedule) {
                /**
                 * @var $scheduledStructure ScheduledStructure
                 * @var $structure DataStructure
                 */
                $this->assertContains($elementName, $schedule['structure']);
                $scheduledStructure->unsetStructureElement($elementName);
                $scheduledStructure->setElement($elementName, []);
                $structure->createStructuralElement($elementName, 'block', 'someClass');
            });

        $this->model->process($this->readerContextMock, $this->generatorContextMock);
        $this->assertEquals($expectedSchedule, $this->scheduledStructure->getElements());
    }

    /**
     * Data provider fo testProcess
     *
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                'schedule' => [
                    'structure' => [
                        'first.element',
                        'second.element',
                        'third.element',
                        'remove.element',
                        'sort.element',
                    ],
                    'move' => [
                        'third.element' => ['second.element', 'sibling', false, 'alias'],
                    ],
                    'remove' => ['remove.element'],
                    'sort' => [
                        'sort.element' => ['second.element', 'sibling', false, 'alias'],
                    ],
                ],
                'expectedScheduledElements' => [
                    'first.element' => [], 'second.element' => [], 'third.element' => [], 'sort.element' => []
                ],
            ],
        ];
    }
}
