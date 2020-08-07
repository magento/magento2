<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\View\Layout;

use Magento\Backend\Model\View\Layout\StructureManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\ScheduledStructure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StructureManagerTest extends TestCase
{
    /**
     * @var Structure|MockObject
     */
    private $structureMock;

    /**
     * @var ScheduledStructure|MockObject
     */
    private $scheduledStructureMock;

    /**
     * @var StructureManager
     */
    private $structureManager;

    protected function setUp(): void
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
