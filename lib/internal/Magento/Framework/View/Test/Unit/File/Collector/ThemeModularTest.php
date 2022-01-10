<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\File\Collector;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\Collector\ThemeModular;
use Magento\Framework\View\File\Factory;
use Magento\Framework\View\Helper\PathPattern;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemeModularTest extends TestCase
{
    /**
     * @var ThemeModular
     */
    private $model;

    /**
     * @var Read|MockObject
     */
    private $themeDirectory;

    /**
     * @var Factory|MockObject
     */
    private $fileFactory;

    /**
     * @var PathPattern|MockObject
     */
    protected $pathPatternHelperMock;

    /**
     * @var ReadFactory|MockObject
     */
    private $readDirFactory;

    /**
     * @var ComponentRegistrarInterface|MockObject
     */
    private $componentRegistrar;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->themeDirectory = $this->createPartialMock(
            Read::class,
            ['getAbsolutePath', 'search']
        );
        $this->pathPatternHelperMock = $this->getMockBuilder(PathPattern::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactory = $this->createMock(Factory::class);
        $this->readDirFactory = $this->createMock(ReadFactory::class);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->themeDirectory);
        $this->componentRegistrar = $this->getMockForAbstractClass(
            ComponentRegistrarInterface::class
        );
        $this->model = new ThemeModular(
            $this->fileFactory,
            $this->readDirFactory,
            $this->componentRegistrar,
            $this->pathPatternHelperMock,
            'subdir'
        );
    }

    /**
     * @return void
     */
    public function testGetFilesWrongTheme(): void
    {
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->willReturn('');
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->once())
            ->method('getFullPath')
            ->willReturn('area/Vendor/theme');
        $this->assertSame([], $this->model->getFiles($theme, ''));
    }

    /**
     * @param array $files
     * @param string $filePath
     * @param string $pathPattern
     *
     * @return void
     * @dataProvider getFilesDataProvider
     */
    public function testGetFiles($files, $filePath, $pathPattern): void
    {
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $themePath = 'area/theme/path';
        $theme->expects($this->once())->method('getFullPath')->willReturn($themePath);

        $handlePath = 'design/area/theme/path/%s/subdir/%s';
        $returnKeys = [];
        foreach ($files as $file) {
            $returnKeys[] = sprintf($handlePath, $file['module'], $file['handle']);
        }

        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $themePath)
            ->willReturn('/full/theme/path');
        $this->pathPatternHelperMock->expects($this->any())
            ->method('translatePatternFromGlob')
            ->with($filePath)
            ->willReturn($pathPattern);
        $this->themeDirectory->expects($this->once())
            ->method('search')
            ->willReturn($returnKeys);
        $this->themeDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnArgument(0);

        $checkResult = [];
        $withArgs = $willReturnArgs = [];

        foreach ($files as $key => $file) {
            $checkResult[$key] = new File($file['handle'], $file['module'], $theme);
            $checkResult[$key] = $this->createMock(File::class);
            $withArgs[] = [sprintf($handlePath, $file['module'], $file['handle']), $file['module'], $theme];
            $willReturnArgs[] = $checkResult[$key];
        }
        $this->fileFactory
            ->method('create')
            ->withConsecutive(...$withArgs)
            ->willReturnOnConsecutiveCalls(...$willReturnArgs);

        $this->assertSame($checkResult, $this->model->getFiles($theme, $filePath));
    }

    /**
     * @return array
     */
    public function getFilesDataProvider(): array
    {
        return [
            [
                [
                    ['handle' => '1.xml', 'module' => 'Module_One'],
                    ['handle' => '2.xml', 'module' => 'Module_One'],
                    ['handle' => '3.xml', 'module' => 'Module_Two']
                ],
                '*.xml',
                '[^/]*\\.xml'
            ],
            [
                [
                    ['handle' => 'preset/4', 'module' => 'Module_Fourth'],
                ],
                'preset/4',
                'preset/4'
            ]
        ];
    }
}
