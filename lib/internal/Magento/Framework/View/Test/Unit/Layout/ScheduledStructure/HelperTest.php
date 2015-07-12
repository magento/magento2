<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout\ScheduledStructure;

use Magento\Framework\View\Layout;

/**
 * Class HelperTest
 * @covers \Magento\Framework\View\Layout\ScheduledStructure\Helper
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduledStructure;

    /**
     * @var \Magento\Framework\View\Layout\Data\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataStructure;

    /**
     * @var Helper
     */
    protected $helper;

    public function setUp()
    {
        $this->scheduledStructure = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataStructure = $this->getMockBuilder('Magento\Framework\View\Layout\Data\Structure')
            ->disableOriginalConstructor()
            ->getMock();

        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $helperObjectManager->getObject('Magento\Framework\View\Layout\ScheduledStructure\Helper');
    }

    /**
     * @param string $currentNodeName
     * @param string $actualNodeName
     * @param int $unsetPathElementCount
     * @param int $unsetStructureElementCount
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

        $this->scheduledStructure->expects($this->once())->method('hasPath')
            ->with($parentNodeName)
            ->will($this->returnValue(true));
        $this->scheduledStructure->expects($this->any())->method('hasStructureElement')
            ->with($actualNodeName)
            ->will($this->returnValue(true));
        $this->scheduledStructure->expects($this->once())->method('setPathElement')
            ->with($actualNodeName, $testPath . '/' . $actualNodeName)
            ->will($this->returnValue(true));
        $this->scheduledStructure->expects($this->once())->method('setStructureElement')
            ->with($actualNodeName, [$block, $currentNodeAs, $parentNodeName, $after, true]);
        $this->scheduledStructure->expects($this->once())->method('getPath')
            ->with($parentNodeName)
            ->will($this->returnValue('test_path'));
        $this->scheduledStructure->expects($this->once())->method('getPaths')
            ->will($this->returnValue([$potentialChild => $testPath . '/' . $currentNodeName . '/']));
        $this->scheduledStructure->expects($this->exactly($unsetPathElementCount))->method('unsetPathElement')
            ->with($potentialChild);
        $this->scheduledStructure->expects($this->exactly($unsetStructureElementCount))->method('unsetStructureElement')
            ->with($potentialChild);

        $currentNode = new \Magento\Framework\View\Layout\Element(
            '<' . $block . ' name="' . $currentNodeName . '" as="' . $currentNodeAs . '" after="' . $after . '"/>'
        );
        $parentNode = new \Magento\Framework\View\Layout\Element('<' . $block . ' name="' . $parentNodeName . '"/>');

        $result = $this->helper->scheduleStructure($this->scheduledStructure, $currentNode, $parentNode);
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

    public function testScheduleNonExistentElement()
    {
        $key = 'key';

        $this->scheduledStructure->expects($this->once())->method('getStructureElement')->with($key)
            ->willReturn([]);
        $this->scheduledStructure->expects($this->once())->method('unsetPathElement')->with($key);
        $this->scheduledStructure->expects($this->once())->method('unsetStructureElement')->with($key);

        $this->helper->scheduleElement($this->scheduledStructure, $this->dataStructure, $key);
    }

    /**
     * @param bool $hasParent
     * @param int $setAsChild
     * @param int $toRemoveList
     *
     * @dataProvider scheduleElementDataProvider
     */
    public function testScheduleElement($hasParent, $setAsChild, $toRemoveList)
    {
        $key = 'key';
        $parentName = 'parent';
        $siblingName = 'sibling';
        $alias = 'alias';
        $block = 'block';
        $data = ['data'];

        $this->scheduledStructure->expects($this->any())
            ->method('getStructureElement')
            ->willReturnMap(
                [
                    [
                        $key,
                        null,
                        [
                            Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_TYPE => $block,
                            Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_ALIAS => $alias,
                            Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME => $parentName,
                            Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME => $siblingName,
                            Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_IS_AFTER => true,
                        ],
                    ],
                    [$parentName, null, []],
                ]
            );
        $this->scheduledStructure->expects($this->any())
            ->method('getStructureElementData')
            ->willReturnMap(
                [
                    [$key, null, $data],
                    [$parentName, null, $data],
                ]
            );
        $this->scheduledStructure->expects($this->any())->method('hasStructureElement')->willReturn(true);
        $this->scheduledStructure->expects($this->once())->method('setElement')->with($key, [$block, $data]);

        $this->dataStructure->expects($this->once())->method('createElement')->with($key, ['type' => $block]);
        $this->dataStructure->expects($this->once())->method('hasElement')->with($parentName)->willReturn($hasParent);
        $this->dataStructure->expects($this->exactly($setAsChild))
            ->method('setAsChild')
            ->with($key, $parentName, $alias)
            ->willReturn(true);

        $this->scheduledStructure->expects($this->exactly($toRemoveList))
            ->method('setElementToBrokenParentList')
            ->with($key);

        $this->helper->scheduleElement($this->scheduledStructure, $this->dataStructure, $key);
    }

    /**
     * @return array
     */
    public function scheduleElementDataProvider()
    {
        return [
            ['hasParent' => true, 'setAsChild' => 1, 'toRemoveList' => 0],
            ['hasParent' => false, 'setAsChild' => 0, 'toRemoveList' => 1],
        ];
    }
}
