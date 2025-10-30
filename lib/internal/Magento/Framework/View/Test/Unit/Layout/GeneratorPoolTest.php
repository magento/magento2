<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Framework\View\Layout\Element
 */
namespace Magento\Framework\View\Test\Unit\Layout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\Data\Structure as DataStructure;
use Magento\Framework\View\Layout\GeneratorInterface;
use Magento\Framework\View\Layout\GeneratorPool;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\ScheduledStructure\Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GeneratorPoolTest extends TestCase
{
    /**
     * @var Helper|MockObject
     */
    protected $helperMock;

    /**
     * @var Context|MockObject
     */
    protected $readerContextMock;

    /**
     * @var \Magento\Framework\View\Layout\Generator\Context|MockObject
     */
    protected $generatorContextMock;

    /**
     * @var ScheduledStructure
     */
    protected $scheduledStructure;

    /**
     * @var \Magento\Framework\View\Layout\Data\Structure|MockObject
     */
    protected $structureMock;

    /**
     * @var GeneratorPool
     */
    protected $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        // ScheduledStructure
        $this->readerContextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledStructure = new ScheduledStructure();
        $this->readerContextMock->expects($this->any())->method('getScheduledStructure')
            ->willReturn($this->scheduledStructure);

        // DataStructure
        $this->generatorContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Generator\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Data\Structure::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['reorderChildElement'])
            ->getMock();
        $this->generatorContextMock->expects($this->any())->method('getStructure')
            ->willReturn($this->structureMock);

        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helperObjectManager = new ObjectManager($this);
        $this->model = $helperObjectManager->getObject(
            GeneratorPool::class,
            [
                'helper' => $this->helperMock,
                'generators' => $this->getGeneratorsMocks()
            ]
        );
    }

    /**
     * @return MockObject[]
     */
    protected function getGeneratorsMocks()
    {
        $firstGenerator = $this->getMockForAbstractClass(GeneratorInterface::class);
        $firstGenerator->expects($this->any())->method('getType')->willReturn('first_generator');
        $firstGenerator->expects($this->atLeastOnce())->method('process');

        $secondGenerator = $this->getMockForAbstractClass(GeneratorInterface::class);
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
        $invocation->willReturnCallback(function ($arg) use ($reorderMap) {
                static $callCount = 0;
                $expectedId = $reorderMap[$callCount][0];
                $callCount++;
            if ($expectedId == $arg) {
                return null;
            }
        });

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
                $scheduledStructure->setElement($elementName, ['block', []]);
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
    public static function processDataProvider()
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
                'expectedSchedule' => [
                    'first.element' => ['block', []],
                    'second.element' => ['block', []],
                    'third.element' => ['block', []],
                    'sort.element' => ['block', []],
                ],
            ],
        ];
    }
}
