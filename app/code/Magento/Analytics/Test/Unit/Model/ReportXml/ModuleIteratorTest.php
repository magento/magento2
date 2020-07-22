<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\ReportXml;

use Magento\Analytics\Model\ReportXml\ModuleIterator;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModuleIteratorTest extends TestCase
{
    /**
     * @var ModuleManager|MockObject
     */
    private $moduleManagerMock;

    /**
     * @var ModuleIterator|MockObject
     */
    private $moduleIterator;

    protected function setUp(): void
    {
        $this->moduleManagerMock = $this->createMock(ModuleManager::class);
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
