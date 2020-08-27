<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\File\Collector;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\Collector\Theme;
use Magento\Framework\View\File\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    /**
     * Theme path
     *
     * @var string
     */
    private $themePath = 'frontend/Magento/theme';

    const FULL_THEME_PATH = '/full/theme/path';

    /**
     * @var Theme
     */
    private $themeFileCollector;

    /**
     * @var Factory|MockObject
     */
    private $fileFactoryMock;

    /**
     * @var ReadInterface|MockObject
     */
    private $themeDirectoryMock;

    /**
     * @var ThemeInterface|MockObject
     */
    private $themeMock;

    /**
     * @var ReadFactory|MockObject
     */
    private $readDirFactory;

    /**
     * @var ComponentRegistrarInterface|MockObject
     */
    private $componentRegistrar;

    protected function setup(): void
    {
        $this->themeDirectoryMock = $this->getMockBuilder(ReadInterface::class)
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeMock = $this->getMockBuilder(ThemeInterface::class)
            ->getMock();
        $this->themeMock->expects($this->once())
            ->method('getFullPath')
            ->willReturn($this->themePath);

        $this->readDirFactory = $this->createMock(ReadFactory::class);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->themeDirectoryMock);
        $this->componentRegistrar = $this->getMockForAbstractClass(
            ComponentRegistrarInterface::class
        );
        $this->themeFileCollector = new Theme(
            $this->fileFactoryMock,
            $this->readDirFactory,
            $this->componentRegistrar
        );
    }

    public function testGetFilesWrongTheme()
    {
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->willReturn('');
        $this->assertSame([], $this->themeFileCollector->getFiles($this->themeMock, ''));
    }

    public function testGetFilesEmpty()
    {
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $this->themePath)
            ->willReturn(self::FULL_THEME_PATH);
        $this->themeDirectoryMock->expects($this->any())
            ->method('search')
            ->with('')
            ->willReturn([]);

        // Verify no files were returned
        $this->assertEquals([], $this->themeFileCollector->getFiles($this->themeMock, ''));
    }

    public function testGetFilesSingle()
    {
        $searchPath = 'css/*.less';
        $filePath = '/some/absolute/path/css/*.less';

        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $this->themePath)
            ->willReturn(self::FULL_THEME_PATH);
        $fileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeDirectoryMock->expects($this->once())
            ->method('search')
            ->with($searchPath)
            ->willReturn(['file']);
        $this->themeDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with('file')
            ->willReturn($filePath);
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with($filePath, null, $this->themeMock)
            ->willReturn($fileMock);

        // One file was returned from search
        $this->assertEquals([$fileMock], $this->themeFileCollector->getFiles($this->themeMock, $searchPath));
    }

    public function testGetFilesMultiple()
    {
        $dirPath = '/Magento_Customer/css/';
        $searchPath = 'css/*.test';

        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $this->themePath)
            ->willReturn(self::FULL_THEME_PATH);
        $fileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnMap(
                [
                    ['fileA.test', $dirPath . 'fileA.test'],
                    ['fileB.tst', $dirPath . 'fileB.tst'],
                    ['fileC.test', $dirPath . 'fileC.test'],
                ]
            );
        // Verifies correct files are searched for
        $this->themeDirectoryMock->expects($this->once())
            ->method('search')
            ->with($searchPath)
            ->willReturn(['fileA.test', 'fileC.test']);
        // Verifies Magento_Customer was correctly produced from directory path
        $this->fileFactoryMock->expects($this->any())
            ->method('create')
            ->with($this->isType('string'), null, $this->themeMock)
            ->willReturn($fileMock);

        // Only two files should be in array, which were returned from search
        $this->assertEquals(
            [$fileMock, $fileMock],
            $this->themeFileCollector->getFiles($this->themeMock, $searchPath)
        );
    }
}
