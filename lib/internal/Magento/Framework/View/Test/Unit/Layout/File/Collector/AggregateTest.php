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
            ->withConsecutive([$parentTheme], [$theme])
            ->willReturnOnConsecutiveCalls([$files[1]], [$files[4]]);
        $this->_overridingBaseFiles
            ->method('getFiles')
            ->withConsecutive([$parentTheme], [$theme])
            ->willReturnOnConsecutiveCalls([$files[2]], [$files[5]]);
        $this->_overridingThemeFiles
            ->method('getFiles')
            ->withConsecutive([$parentTheme], [$theme])
            ->willReturnOnConsecutiveCalls([$files[3]], [$files[6]]);

        $this->_fileList
            ->method('add')
            ->withConsecutive([[$files[0]]], [[$files[1]]], [[$files[4]]]);
        $this->_fileList
            ->method('replace')
            ->withConsecutive([[$files[2]]], [[$files[3]]], [[$files[5]]], [[$files[6]]]);

        $this->_fileList->expects($this->atLeastOnce())->method('getAll')->willReturn($files);

        $this->assertSame($files, $this->_model->getFiles($theme, '*'));
    }
}
