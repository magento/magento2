<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout\ScheduledStructure;

use Magento\Framework\View\Layout;
use Magento\Framework\App\State;

/**
 * Class HelperTest
 * @covers \Magento\Framework\View\Layout\ScheduledStructure\Helper
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduledStructureMock;

    /**
     * @var \Magento\Framework\View\Layout\Data\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataStructureMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure\Helper
     */
    protected $helper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->scheduledStructureMock = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataStructureMock = $this->getMockBuilder('Magento\Framework\View\Layout\Data\Structure')
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->stateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);

        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $helperObjectManager->getObject(
            'Magento\Framework\View\Layout\ScheduledStructure\Helper',
            [
                'logger' => $this->loggerMock,
                'state' => $this->stateMock
            ]
        );
    }

    /**
     * @param string $currentNodeName
     * @param string $actualNodeName
     * @param int $unsetPathElementCount
     * @param int $unsetStructureElementCount
     * @return void
     *
     * @dataProvider scheduleStructureDataProvider
     */
    public function testScheduleStructure(
        $currentNodeName,
        $actualNodeName,
        $unsetPathElementCount,
        $unsetStructureElementCount
    ) {
        $parentNodeName = 'parent_node';
        $currentNodeAs = 'currentNode';
        $after = 'after';
        $block = 'block';
        $testPath = 'test_path';
        $potentialChild = 'potential_child';

        $this->scheduledStructureMock->expects($this->once())->method('hasPath')
            ->with($parentNodeName)
            ->will($this->returnValue(true));
        $this->scheduledStructureMock->expects($this->any())->method('hasStructureElement')
            ->with($actualNodeName)
            ->will($this->returnValue(true));
        $this->scheduledStructureMock->expects($this->once())->method('setPathElement')
            ->with($actualNodeName, $testPath . '/' . $actualNodeName)
            ->will($this->returnValue(true));
        $this->scheduledStructureMock->expects($this->once())->method('setStructureElement')
            ->with($actualNodeName, [$block, $currentNodeAs, $parentNodeName, $after, true]);
        $this->scheduledStructureMock->expects($this->once())->method('getPath')
            ->with($parentNodeName)
            ->will($this->returnValue('test_path'));
        $this->scheduledStructureMock->expects($this->once())->method('getPaths')
            ->will($this->returnValue([$potentialChild => $testPath . '/' . $currentNodeName . '/']));
        $this->scheduledStructureMock->expects($this->exactly($unsetPathElementCount))->method('unsetPathElement')
            ->with($potentialChild);
        $this->scheduledStructureMock->expects($this->exactly($unsetStructureElementCount))
            ->method('unsetStructureElement')
            ->with($potentialChild);

        $currentNode = new \Magento\Framework\View\Layout\Element(
            '<' . $block . ' name="' . $currentNodeName . '" as="' . $currentNodeAs . '" after="' . $after . '"/>'
        );
        $parentNode = new \Magento\Framework\View\Layout\Element('<' . $block . ' name="' . $parentNodeName . '"/>');

        $result = $this->helper->scheduleStructure($this->scheduledStructureMock, $currentNode, $parentNode);
        $this->assertEquals($actualNodeName, $result);
    }

    /**
     * @return array
     */
    public function scheduleStructureDataProvider()
    {
        return [
            ['current_node', 'current_node', 1, 1],
            ['', 'parent_node_schedule_block0', 0, 0]
        ];
    }

    /**
     * @return void
     */
    public function testScheduleNonExistentElement()
    {
        $key = 'key';

        $this->scheduledStructureMock->expects($this->once())->method('getStructureElement')->with($key)
            ->willReturn([]);
        $this->scheduledStructureMock->expects($this->once())->method('unsetPathElement')->with($key);
        $this->scheduledStructureMock->expects($this->once())->method('unsetStructureElement')->with($key);

        $this->helper->scheduleElement($this->scheduledStructureMock, $this->dataStructureMock, $key);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $loggerExpects
     * @param string $stateMode
     * @return void
     * @dataProvider scheduleElementLogDataProvider
     */
    public function testScheduleElementLog($loggerExpects, $stateMode)
    {
        $key = 'key';
        $parentName = 'parent';
        $alias = 'alias';
        $block = 'block';
        $siblingName = null;
        $isAfter = false;

        $this->scheduledStructureMock->expects($this->once())
            ->method('getStructureElement')
            ->willReturn(
                [
                    Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_TYPE => $block,
                    Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_ALIAS => $alias,
                    Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME => $parentName,
                    Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME => $siblingName,
                    Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_IS_AFTER => $isAfter
                ]
            );
        $this->scheduledStructureMock->expects($this->once())
            ->method('hasStructureElement')
            ->with($parentName)
            ->willReturn(false);
        $this->dataStructureMock->expects($this->once())
            ->method('hasElement')
            ->with($parentName)
            ->willReturn(false);
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($stateMode);
        $this->loggerMock->expects($loggerExpects)
            ->method('info')
            ->with(
                "Broken reference: the '{$key}' element cannot be added as child to '{$parentName}', " .
                'because the latter doesn\'t exist'
            );

        $this->helper->scheduleElement($this->scheduledStructureMock, $this->dataStructureMock, $key);
    }

    /**
     * @return array
     */
    public function scheduleElementLogDataProvider()
    {
        return [
            [
                'loggerExpects' => $this->once(),
                'stateMode' => State::MODE_DEVELOPER
            ],
            [
                'loggerExpects' => $this->never(),
                'stateMode' => State::MODE_DEFAULT
            ],
            [
                'loggerExpects' => $this->never(),
                'stateMode' => State::MODE_PRODUCTION
            ]
        ];
    }

    /**
     * @param bool $hasParent
     * @param int $setAsChild
     * @param int $toRemoveList
     * @param string $siblingName
     * @param bool $isAfter
     * @param int $toSortList
     * @return void
     *
     * @dataProvider scheduleElementDataProvider
     */
    public function testScheduleElement($hasParent, $setAsChild, $toRemoveList, $siblingName, $isAfter, $toSortList)
    {
        $key = 'key';
        $parentName = 'parent';
        $alias = 'alias';
        $block = 'block';
        $data = ['data'];

        $this->scheduledStructureMock->expects($this->any())
            ->method('getStructureElement')
            ->willReturnMap([
                [
                    $key,
                    null,
                    [
                        Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_TYPE => $block,
                        Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_ALIAS => $alias,
                        Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME => $parentName,
                        Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME => $siblingName,
                        Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_IS_AFTER => $isAfter,
                    ],
                ],
                [$parentName, null, []],
            ]);
        $this->scheduledStructureMock->expects($this->any())
            ->method('getStructureElementData')
            ->willReturnMap(
                [
                    [$key, null, $data],
                    [$parentName, null, $data],
                ]
            );
        $this->scheduledStructureMock->expects($this->any())->method('hasStructureElement')->willReturn(true);
        $this->scheduledStructureMock->expects($this->once())->method('setElement')->with($key, [$block, $data]);
        $this->dataStructureMock->expects($this->once())->method('createElement')->with($key, ['type' => $block]);
        $this->dataStructureMock->expects($this->once())
            ->method('hasElement')
            ->with($parentName)
            ->willReturn($hasParent);
        $this->dataStructureMock->expects($this->exactly($setAsChild))
            ->method('setAsChild')
            ->with($key, $parentName, $alias)
            ->willReturn(true);
        $this->scheduledStructureMock->expects($this->exactly($toRemoveList))
            ->method('setElementToBrokenParentList')
            ->with($key);
        $this->scheduledStructureMock->expects($this->exactly($toSortList))
            ->method('setElementToSortList')
            ->with($parentName, $key, $siblingName, $isAfter);

        $this->helper->scheduleElement($this->scheduledStructureMock, $this->dataStructureMock, $key);
    }

    /**
     * @return array
     */
    public function scheduleElementDataProvider()
    {
        return [
            [
                'hasParent' => true,
                'setAsChild' => 1,
                'toRemoveList' => 0,
                'siblingName' => 'sibling',
                'isAfter' => false,
                'toSortList' => 1,
            ],
            [
                'hasParent' => false,
                'setAsChild' => 0,
                'toRemoveList' => 1,
                'siblingName' => null,
                'isAfter' => false,
                'toSortList' => 0,
            ]
        ];
    }
}
