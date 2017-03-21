<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\File\Collector;

class AggregateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\File\Collector\Aggregated
     */
    private $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_fileList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_baseFiles;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_themeFiles;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_overridingBaseFiles;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_overridingThemeFiles;

    protected function setUp()
    {
        $this->_fileList = $this->getMock(\Magento\Framework\View\File\FileList::class, [], [], '', false);
        $this->_baseFiles = $this->getMockForAbstractClass(\Magento\Framework\View\File\CollectorInterface::class);
        $this->_themeFiles = $this->getMockForAbstractClass(\Magento\Framework\View\File\CollectorInterface::class);
        $this->_overridingBaseFiles = $this->getMockForAbstractClass(
            \Magento\Framework\View\File\CollectorInterface::class
        );
        $this->_overridingThemeFiles = $this->getMockForAbstractClass(
            \Magento\Framework\View\File\CollectorInterface::class
        );
        $fileListFactory = $this->getMock(
            \Magento\Framework\View\File\FileList\Factory::class,
            [],
            [],
            '',
            false
        );
        $fileListFactory->expects($this->once())->method('create')->will($this->returnValue($this->_fileList));
        $this->_model = new \Magento\Framework\View\Layout\File\Collector\Aggregated(
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
        $parentTheme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme->expects(
            $this->once()
        )->method(
            'getInheritedThemes'
        )->will(
            $this->returnValue([$parentTheme, $parentTheme])
        );

        $files = [
            new \Magento\Framework\View\File('0.xml', 'Module_One'),
            new \Magento\Framework\View\File('1.xml', 'Module_One', $parentTheme),
            new \Magento\Framework\View\File('2.xml', 'Module_One', $parentTheme),
            new \Magento\Framework\View\File('3.xml', 'Module_One', $parentTheme),
            new \Magento\Framework\View\File('4.xml', 'Module_One', $theme),
            new \Magento\Framework\View\File('5.xml', 'Module_One', $theme),
            new \Magento\Framework\View\File('6.xml', 'Module_One', $theme),
        ];

        $this->_baseFiles->expects(
            $this->once()
        )->method(
            'getFiles'
        )->with(
            $theme
        )->will(
            $this->returnValue([$files[0]])
        );

        $this->_themeFiles->expects(
            $this->at(0)
        )->method(
            'getFiles'
        )->with(
            $parentTheme
        )->will(
            $this->returnValue([$files[1]])
        );
        $this->_overridingBaseFiles->expects(
            $this->at(0)
        )->method(
            'getFiles'
        )->with(
            $parentTheme
        )->will(
            $this->returnValue([$files[2]])
        );
        $this->_overridingThemeFiles->expects(
            $this->at(0)
        )->method(
            'getFiles'
        )->with(
            $parentTheme
        )->will(
            $this->returnValue([$files[3]])
        );

        $this->_themeFiles->expects(
            $this->at(1)
        )->method(
            'getFiles'
        )->with(
            $theme
        )->will(
            $this->returnValue([$files[4]])
        );
        $this->_overridingBaseFiles->expects(
            $this->at(1)
        )->method(
            'getFiles'
        )->with(
            $theme
        )->will(
            $this->returnValue([$files[5]])
        );
        $this->_overridingThemeFiles->expects(
            $this->at(1)
        )->method(
            'getFiles'
        )->with(
            $theme
        )->will(
            $this->returnValue([$files[6]])
        );

        $this->_fileList->expects($this->at(0))->method('add')->with([$files[0]]);
        $this->_fileList->expects($this->at(1))->method('add')->with([$files[1]]);
        $this->_fileList->expects($this->at(2))->method('replace')->with([$files[2]]);
        $this->_fileList->expects($this->at(3))->method('replace')->with([$files[3]]);
        $this->_fileList->expects($this->at(4))->method('add')->with([$files[4]]);
        $this->_fileList->expects($this->at(5))->method('replace')->with([$files[5]]);
        $this->_fileList->expects($this->at(6))->method('replace')->with([$files[6]]);

        $this->_fileList->expects($this->atLeastOnce())->method('getAll')->will($this->returnValue($files));

        $this->assertSame($files, $this->_model->getFiles($theme, '*'));
    }
}
