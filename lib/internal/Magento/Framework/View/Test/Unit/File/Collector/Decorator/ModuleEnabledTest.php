<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\File\Collector\Decorator;

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\Collector\Decorator\ModuleEnabled;
use Magento\Framework\View\File\CollectorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModuleEnabledTest extends TestCase
{
    /**
     * @var ModuleEnabled
     */
    private $model;

    /**
     * @var CollectorInterface|MockObject
     */
    private $fileSourceMock;

    /**
     * @var ModuleManager|MockObject
     */
    private $moduleManagerMock;

    protected function setUp(): void
    {
        $this->fileSourceMock = $this->getMockForAbstractClass(CollectorInterface::class);
        $this->moduleManagerMock = $this->createMock(ModuleManager::class);
        $this->moduleManagerMock
            ->expects($this->any())
            ->method('isEnabled')
            ->will(
                $this->returnValueMap(
                    [
                        ['Module_Enabled', true],
                        ['Module_Disabled', false],
                    ]
                )
            );
        $this->model = new ModuleEnabled(
            $this->fileSourceMock,
            $this->moduleManagerMock
        );
    }

    public function testGetFiles(): void
    {
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $fileOne = new File('1.xml', 'Module_Enabled');
        $fileTwo = new File('2.xml', 'Module_Disabled');
        $fileThree = new File('3.xml', 'Module_Enabled', $theme);
        $this->fileSourceMock->expects($this->once())
            ->method('getFiles')
            ->with($theme, '*.xml')
            ->willReturn([$fileOne, $fileTwo, $fileThree]);
        $this->assertSame([$fileOne, $fileThree], $this->model->getFiles($theme, '*.xml'));
    }
}
