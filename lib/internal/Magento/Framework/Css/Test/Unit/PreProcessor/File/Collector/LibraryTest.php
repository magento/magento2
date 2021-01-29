<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\Test\Unit\PreProcessor\File\Collector;

use Magento\Framework\Component\ComponentRegistrar;
use \Magento\Framework\Css\PreProcessor\File\Collector\Library;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Tests Library
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LibraryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Library
     */
    private $library;

    /**
     * @var \Magento\Framework\View\File\FileList\Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileListFactoryMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileSystemMock;

    /**
     * @var \Magento\Framework\View\File\Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\Framework\View\File\FileList|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileListMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $libraryDirectoryMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $readFactoryMock;

    /**
     * Component registry
     *
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $componentRegistrarMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeMock;

    /**
     * Setup tests
     * @return void
     */
    protected function setup(): void
    {
        $this->fileListFactoryMock = $this->getMockBuilder(\Magento\Framework\View\File\FileList\Factory::class)
            ->disableOriginalConstructor()->getMock();
        $this->fileListMock = $this->getMockBuilder(\Magento\Framework\View\File\FileList::class)
            ->disableOriginalConstructor()->getMock();
        $this->fileListFactoryMock->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Css\PreProcessor\File\FileList\Collator::class)
            ->willReturn($this->fileListMock);
        $this->readFactoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->componentRegistrarMock = $this->getMockBuilder(
            \Magento\Framework\Component\ComponentRegistrarInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->fileSystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->libraryDirectoryMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\Directory\ReadInterface::class
        )->getMock();
        $this->fileSystemMock->expects($this->any())->method('getDirectoryRead')
            ->willReturnMap(
                
                    [
                        [DirectoryList::LIB_WEB, Filesystem\DriverPool::FILE, $this->libraryDirectoryMock],
                    ]
                
            );

        $this->fileFactoryMock = $this->getMockBuilder(\Magento\Framework\View\File\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeMock = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)->getMock();
        $this->library = new Library(
            $this->fileListFactoryMock,
            $this->fileSystemMock,
            $this->fileFactoryMock,
            $this->readFactoryMock,
            $this->componentRegistrarMock
        );
    }

    public function testGetFilesEmpty()
    {
        $this->libraryDirectoryMock->expects($this->any())->method('search')->willReturn([]);
        $this->themeMock->expects($this->any())->method('getInheritedThemes')->willReturn([]);

        // Verify search/replace are never called if no inheritedThemes
        $this->readFactoryMock->expects($this->never())
            ->method('create');
        $this->componentRegistrarMock->expects($this->never())
            ->method('getPath');

        $this->library->getFiles($this->themeMock, '*');
    }

    /**
     *
     * @dataProvider getFilesDataProvider
     *
     * @param array $libraryFiles Files in lib directory
     * @param array $themeFiles Files in theme
     * *
     * @return void
     */
    public function testGetFiles($libraryFiles, $themeFiles)
    {
        $this->fileListMock->expects($this->any())->method('getAll')->willReturn(['returnedFile']);

        $this->libraryDirectoryMock->expects($this->any())->method('search')->willReturn($libraryFiles);
        $this->libraryDirectoryMock->expects($this->any())->method('getAbsolutePath')->willReturnCallback(
            function ($file) {
                return '/opt/Magento/lib/' . $file;
            }
        );
        $themePath = '/var/Magento/ATheme';
        $subPath = '*';
        $readerMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)->getMock();
        $this->readFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($readerMock);
        $this->componentRegistrarMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $themePath)
            ->willReturn(['/path/to/theme']);
        $readerMock->expects($this->once())
            ->method('search')
            ->willReturn($themeFiles);
        $inheritedThemeMock = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)->getMock();
        $inheritedThemeMock->expects($this->any())->method('getFullPath')->willReturn($themePath);
        $this->themeMock->expects($this->any())->method('getInheritedThemes')
            ->willReturn([$inheritedThemeMock]);
        $this->assertEquals(['returnedFile'], $this->library->getFiles($this->themeMock, $subPath));
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
