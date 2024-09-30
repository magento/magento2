<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\File\Collector;

use Magento\Framework\Component\ComponentFile;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\Collector\Base;
use Magento\Framework\View\File\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    /**
     * @var Base
     */
    private $fileCollector;

    /**
     * @var Factory|MockObject
     */
    private $fileFactoryMock;

    /**
     * @var ThemeInterface|MockObject
     */
    private $themeMock;

    /**
     * @var DirSearch|MockObject
     */
    private $dirSearch;

    protected function setUp(): void
    {
        $this->fileFactoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeMock = $this->getMockBuilder(ThemeInterface::class)
            ->setMethods(['getData'])
            ->getMockForAbstractClass();

        $this->dirSearch = $this->createMock(DirSearch::class);

        $this->fileCollector = new Base(
            $this->dirSearch,
            $this->fileFactoryMock,
            'layout'
        );
    }

    public function testGetFiles()
    {
        $files = [];
        foreach (['shared', 'theme'] as $fileType) {
            for ($i = 0; $i < 2; $i++) {
                $file = $this->createMock(ComponentFile::class);
                $file->expects($this->once())
                    ->method('getFullPath')
                    ->willReturn("{$fileType}/module/{$i}/path");
                $file->expects($this->once())
                    ->method('getComponentName')
                    ->willReturn('Module_' . $i);
                $files[$fileType][] = $file;
            }
        }

        $this->dirSearch->expects($this->any())
            ->method('collectFilesWithContext')
            ->willReturnMap(
                [
                    [ComponentRegistrar::MODULE, 'view/base/layout/*.xml', $files['shared']],
                    [ComponentRegistrar::MODULE, 'view/frontend/layout/*.xml', $files['theme']]
                ]
            );
        $this->fileFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->createFileMock());
        $this->themeMock->expects($this->once())
            ->method('getArea')
            ->willReturn('frontend');

        $result = $this->fileCollector->getFiles($this->themeMock, '*.xml');
        $this->assertCount(4, $result);
        $this->assertInstanceOf(File::class, $result[0]);
        $this->assertInstanceOf(File::class, $result[1]);
        $this->assertInstanceOf(File::class, $result[2]);
        $this->assertInstanceOf(File::class, $result[3]);
    }

    /**
     * Create file mock object
     *
     * @return File|MockObject
     */
    protected function createFileMock()
    {
        return $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
