<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Less\File\Collector;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Tests Library
 */
class LibraryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\File\FileList\Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileListFactoryMock;

    /**
     * @var \Magento\Framework\Filesystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileSystemMock;

    /**
     * @var \Magento\Framework\View\File\Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\Framework\View\File\FileList|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileListMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $libraryDirectoryMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $themesDirectoryMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeMock;

    /**
     * Setup tests
     * @return void
     */
    public function setup()
    {
        $this->fileListFactoryMock = $this->getMockBuilder('Magento\Framework\View\File\FileList\Factory')
            ->disableOriginalConstructor()->getMock();
        $this->fileListMock = $this->getMockBuilder('Magento\Framework\View\File\FileList')
            ->disableOriginalConstructor()->getMock();
        $this->fileListFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($this->fileListMock));

        $this->fileSystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->libraryDirectoryMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->getMock();
        $this->themesDirectoryMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->getMock();
        $this->fileSystemMock->expects($this->any())->method('getDirectoryRead')
            ->will(
                $this->returnValueMap(
                    [
                        [DirectoryList::LIB_WEB, $this->libraryDirectoryMock],
                        [DirectoryList::THEMES, $this->themesDirectoryMock],
                    ]
                )
            );

        $this->fileFactoryMock = $this->getMockBuilder('Magento\Framework\View\File\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeMock = $this->getMockBuilder('\Magento\Framework\View\Design\ThemeInterface')->getMock();
    }

    public function testGetFilesEmpty()
    {
        $this->libraryDirectoryMock->expects($this->any())->method('search')->will($this->returnValue([]));
        $this->themeMock->expects($this->any())->method('getInheritedThemes')->will($this->returnValue([]));

        // Verify search/replace are never called if no inheritedThemes
        $this->themesDirectoryMock->expects($this->never())->method('search');
        $this->fileListMock->expects($this->never())->method('replace');

        $library = new Library(
            $this->fileListFactoryMock,
            $this->fileSystemMock,
            $this->fileFactoryMock
        );
        $library->getFiles($this->themeMock, '*');
    }

    /**
     *
     * @dataProvider getFilesDataProvider
     *
     * @param $libraryFiles array Files in lib directory
     * @param $themeFiles array Files in theme
     * *
     * @return void
     */
    public function testGetFiles($libraryFiles, $themeFiles)
    {
        $this->fileListMock->expects($this->any())->method('getAll')->will($this->returnValue(['returnedFile']));

        $this->libraryDirectoryMock->expects($this->any())->method('search')->will($this->returnValue($libraryFiles));
        $this->libraryDirectoryMock->expects($this->any())->method('getAbsolutePath')->will($this->returnCallback(
            function ($file) {
                    return '/opt/Magneto/lib/' . $file;
            }
        ));
        $themePath = '/var/Magento/ATheme';
        $subPath = '*';

        $this->themesDirectoryMock->expects($this->any())
            ->method('search')
            ->with($themePath . '/web/' . $subPath)
            ->will($this->returnValue($themeFiles));

        $library = new Library(
            $this->fileListFactoryMock,
            $this->fileSystemMock,
            $this->fileFactoryMock
        );

        $inheritedThemeMock = $this->getMockBuilder('\Magento\Framework\View\Design\ThemeInterface')->getMock();
        $inheritedThemeMock->expects($this->any())->method('getFullPath')->will($this->returnValue($themePath));
        $this->themeMock->expects($this->any())->method('getInheritedThemes')
            ->will($this->returnValue([$inheritedThemeMock]));

        $this->assertEquals(['returnedFile'], $library->getFiles($this->themeMock, $subPath));
    }

    /**
     * Provides test data for testGetFiles()
     *
     * @return array
     */
    public function getFilesDataProvider()
    {
        return [
            'all files' => [['file1'], ['file2']],
            'no library' => [[], ['file1', 'file2']],
        ];
    }
}
