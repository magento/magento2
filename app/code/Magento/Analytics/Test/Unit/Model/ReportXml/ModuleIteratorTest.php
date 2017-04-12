<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\ReportXml;

use Magento\Analytics\Model\ReportXml\ModuleIterator;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ModuleIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleManagerMock;

    /**
     * @var ModuleIterator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleIterator;

    public function setUp()
    {
        $this->moduleManagerMock = $this->getMockBuilder(ModuleManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->moduleIterator = $objectManagerHelper->getObject(
            ModuleIterator::class,
            [
                'moduleManager' => $this->moduleManagerMock,
                'iterator' => new \ArrayIterator([0 => ['module_name' => 'Coco_Module']])
            ]
        );
    }

    public function testCurrent()
    {
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Coco_Module')
            ->willReturn(true);
        foreach ($this->moduleIterator as $item) {
            $this->assertEquals(['module_name' => 'Coco_Module', 'status' => 'Enabled'], $item);
        }
    }
}
