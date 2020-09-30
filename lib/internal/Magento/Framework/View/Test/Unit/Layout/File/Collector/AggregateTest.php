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
    public function testGetFiles()
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
            new File('6.xml', 'Module_One', $theme),
        ];

        $this->_baseFiles->expects(
            $this->once()
        )->method(
            'getFiles'
        )->with(
            $theme
        )->willReturn(
            [$files[0]]
        );

        $this->_themeFiles->expects(
            $this->at(0)
        )->method(
            'getFiles'
        )->with(
            $parentTheme
        )->willReturn(
            [$files[1]]
        );
        $this->_overridingBaseFiles->expects(
            $this->at(0)
        )->method(
            'getFiles'
        )->with(
            $parentTheme
        )->willReturn(
            [$files[2]]
        );
        $this->_overridingThemeFiles->expects(
            $this->at(0)
        )->method(
            'getFiles'
        )->with(
            $parentTheme
        )->willReturn(
            [$files[3]]
        );

        $this->_themeFiles->expects(
            $this->at(1)
        )->method(
            'getFiles'
        )->with(
            $theme
        )->willReturn(
            [$files[4]]
        );
        $this->_overridingBaseFiles->expects(
            $this->at(1)
        )->method(
            'getFiles'
        )->with(
            $theme
        )->willReturn(
            [$files[5]]
        );
        $this->_overridingThemeFiles->expects(
            $this->at(1)
        )->method(
            'getFiles'
        )->with(
            $theme
        )->willReturn(
            [$files[6]]
        );

        $this->_fileList->expects($this->at(0))->method('add')->with([$files[0]]);
        $this->_fileList->expects($this->at(1))->method('add')->with([$files[1]]);
        $this->_fileList->expects($this->at(2))->method('replace')->with([$files[2]]);
        $this->_fileList->expects($this->at(3))->method('replace')->with([$files[3]]);
        $this->_fileList->expects($this->at(4))->method('add')->with([$files[4]]);
        $this->_fileList->expects($this->at(5))->method('replace')->with([$files[5]]);
        $this->_fileList->expects($this->at(6))->method('replace')->with([$files[6]]);

        $this->_fileList->expects($this->atLeastOnce())->method('getAll')->willReturn($files);

        $this->assertSame($files, $this->_model->getFiles($theme, '*'));
    }
}
