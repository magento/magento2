<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\File\Collector;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File\FileList;
use Magento\Framework\View\File\FileList\Factory;
use Magento\Framework\View\Layout\File\Collector\Aggregated;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AggregateTest extends TestCase
{
    /**
     * @var Aggregated
     */
    private $_model;

    /**
     * @var MockObject
     */
    private $_fileList;

    /**
     * @var MockObject
     */
    private $_baseFiles;

    /**
     * @var MockObject
     */
    private $_themeFiles;

    /**
     * @var MockObject
     */
    private $_overridingBaseFiles;

    /**
     * @var MockObject
     */
    private $_overridingThemeFiles;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_fileList = $this->createMock(FileList::class);
        $this->_baseFiles = $this->getMockForAbstractClass(CollectorInterface::class);
        $this->_themeFiles = $this->getMockForAbstractClass(CollectorInterface::class);
        $this->_overridingBaseFiles = $this->getMockForAbstractClass(
            CollectorInterface::class
        );
        $this->_overridingThemeFiles = $this->getMockForAbstractClass(
            CollectorInterface::class
        );
        $fileListFactory = $this->createMock(Factory::class);
        $fileListFactory->expects($this->once())->method('create')->willReturn($this->_fileList);
        $this->_model = new Aggregated(
            $fileListFactory,
            $this->_baseFiles,
            $this->_themeFiles,
            $this->_overridingBaseFiles,
            $this->_overridingThemeFiles
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetFiles(): void
    {
        $parentTheme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects(
            $this->once()
        )->method(
            'getInheritedThemes'
        )->willReturn(
            [$parentTheme, $parentTheme]
        );

        $files = [
            new File('0.xml', 'Module_One'),
            new File('1.xml', 'Module_One', $parentTheme),
            new File('2.xml', 'Module_One', $parentTheme),
            new File('3.xml', 'Module_One', $parentTheme),
            new File('4.xml', 'Module_One', $theme),
            new File('5.xml', 'Module_One', $theme),
            new File('6.xml', 'Module_One', $theme)
        ];

        $this->_baseFiles->expects($this->once())
            ->method('getFiles')
            ->with($theme)
            ->willReturn([$files[0]]);

        $this->_themeFiles
            ->method('getFiles')
            ->willReturnCallback(
                function ($arg) use ($parentTheme, $theme, $files) {
                    if ($arg == $parentTheme) {
                        return [$files[1]];
                    } elseif ($arg == $theme) {
                        return [$files[4]];
                    }
                }
            );
        $this->_overridingBaseFiles
            ->method('getFiles')
            ->willReturnCallback(
                function ($arg) use ($parentTheme, $theme, $files) {
                    if ($arg == $parentTheme) {
                        return [$files[2]];
                    } elseif ($arg == $theme) {
                        return [$files[5]];
                    }
                }
            );
        $this->_overridingThemeFiles
            ->method('getFiles')
            ->willReturnCallback(
                function ($arg) use ($parentTheme, $theme, $files) {
                    if ($arg == $parentTheme) {
                        return [$files[3]];
                    } elseif ($arg == $theme) {
                        return [$files[6]];
                    }
                }
            );
        $this->_fileList
            ->method('add')
            ->willReturnCallback(
                function ($arg) use ($files) {
                    if ($arg == [$files[0]] || $arg == [$files[1]] || $arg == [$files[4]]) {
                        return null;
                    }
                }
            );

        $this->_fileList
            ->method('replace')
            ->willReturnCallback(
                function ($arg) use ($files) {
                    if ($arg == [$files[2]] || $arg == [$files[3]] || $arg == [$files[5]] || $arg == [$files[6]]) {
                        return null;
                    }
                }
            );

        $this->_fileList->expects($this->atLeastOnce())->method('getAll')->willReturn($files);

        $this->assertSame($files, $this->_model->getFiles($theme, '*'));
    }
}
