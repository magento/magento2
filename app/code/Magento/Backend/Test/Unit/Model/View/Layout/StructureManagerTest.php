<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Model\View\Layout;

use Magento\Backend\Model\View\Layout\StructureManager;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class StructureManagerTest
 */
class StructureManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * @var ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduledStructureMock;

    /**
     * @var StructureManager
     */
    private $structureManager;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledStructureMock = $this->getMockBuilder(ScheduledStructure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureManager = $objectManager->getObject(StructureManager::class);
    }

    public function testRemoveElement()
    {
        $this->structureMock->expects($this->exactly(3))
            ->method('getChildren')
            ->willReturnMap(
                [
                    [
                    'element-0', [
                        'element-1' => [],
                        'element-2' => []
                        ]
                    ],
                    [
                        'element-1', []
                    ],
                    [
                        'element-2', []
                    ]
                ]
            );
        $this->scheduledStructureMock->expects($this->exactly(3))
            ->method('unsetElement')
            ->willReturnMap(
                [
                    ['element-0', true],
                    ['element-1', true],
                    ['element-2', true]
                ]
            );
        $this->structureMock->expects($this->once())
            ->method('unsetElement')
            ->with('element-0');
        $this->assertTrue(
            $this->structureManager->removeElement(
                $this->scheduledStructureMock,
                $this->structureMock,
                'element-0',
                false
            )
        );
    }
}
