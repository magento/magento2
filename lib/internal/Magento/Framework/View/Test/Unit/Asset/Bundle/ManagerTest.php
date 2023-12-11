<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset\Bundle;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\View;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Asset\Bundle;
use Magento\Framework\View\Asset\Bundle\ConfigInterface;
use Magento\Framework\View\Asset\Bundle\Manager;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Minification;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    /** @var  Manager|MockObject */
    protected $manager;

    /** @var  Filesystem|MockObject */
    protected $filesystem;

    /** @var  Bundle|MockObject */
    protected $bundle;

    /** @var  ConfigInterface|MockObject */
    protected $bundleConfig;

    /** @var  \Magento\Framework\View\Asset\ConfigInterface|MockObject */
    protected $assetConfig;

    /** @var  LocalInterface|MockObject */
    protected $asset;

    /** @var Minification|MockObject */
    private $minificationMock;

    protected function setUp(): void
    {
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundle = $this->getMockBuilder(Bundle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleConfig = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assetConfig = $this->getMockBuilder(\Magento\Framework\View\Asset\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->asset = $this->getMockForAbstractClass(
            LocalInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getContentType']
        );

        $this->minificationMock = $this->getMockBuilder(Minification::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new Manager(
            $this->filesystem,
            $this->bundle,
            $this->bundleConfig,
            $this->assetConfig,
            $this->minificationMock
        );
    }

    public function testAddAssetWithInvalidType()
    {
        $this->asset->expects($this->once())
            ->method('getContentType')
            ->willReturn('testType');

        $this->assertFalse($this->manager->addAsset($this->asset));
    }

    public function testAddAssetWithExcludedFile()
    {
        $dirRead = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $context = $this->getMockBuilder(FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configView = $this->getMockBuilder(View::class)
            ->setMockClassName('configView')
            ->disableOriginalConstructor()
            ->getMock();

        $this->asset->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);
        $this->asset->expects($this->atLeastOnce())
            ->method('getContentType')
            ->willReturn('js');
        $this->asset->expects($this->atLeastOnce())
            ->method('getModule')
            ->willReturn('Lib');
        $this->asset->expects($this->atLeastOnce())
            ->method('getSourceFile')
            ->willReturn('source/file.min.js');
        $this->asset->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('source/file.min.js');
        $this->asset->expects($this->once())
            ->method('getPath')
            ->willReturn('some/path/to_file');
        $dirRead->expects($this->once())
            ->method('getAbsolutePath')
            ->with('some/path/to_file')
            ->willReturn('some/path/to_file');
        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::APP)
            ->willReturn($dirRead);
        $this->bundleConfig->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with($context)
            ->willReturn($configView);
        $configView->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn(['Lib:' . ':source/file.min.js']);

        $this->assertFalse($this->manager->addAsset($this->asset));
    }

    public function testAddAssetWithExcludedDirectory()
    {
        $dirRead = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $context = $this->getMockBuilder(FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configView = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::APP)
            ->willReturn($dirRead);
        $dirRead->expects($this->once())
            ->method('getAbsolutePath')
            ->with('/path/to/file.js')
            ->willReturn(true);
        $this->asset->expects($this->atLeastOnce())
            ->method('getSourceFile')
            ->willReturn('/path/to/source/file.min.js');
        $this->asset->expects($this->atLeastOnce())
            ->method('getContentType')
            ->willReturn('js');
        $this->asset->expects($this->once())
            ->method('getPath')
            ->willReturn('/path/to/file.js');
        $this->asset->expects($this->atLeastOnce())
            ->method('getModule')
            ->willReturn('');
        $this->asset->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('file/path.js');
        $this->asset->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);
        $this->asset->method('getPath')
            ->willReturn('');   // PHP 8.1. compatibility
        $this->bundleConfig->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with($context)
            ->willReturn($configView);
        $configView->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $configView->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn(['Lib:' . ':file']);

        $this->assertFalse($this->manager->addAsset($this->asset));
    }

    public function testAddAsset()
    {
        $dirRead = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAbsolutePath'])
            ->getMockForAbstractClass();
        $context = $this->getMockBuilder(FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configView = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::APP)
            ->willReturn($dirRead);
        $dirRead->method('getAbsolutePath')
            ->willReturn('some/excluded/file');
        $this->asset->expects($this->atLeastOnce())
            ->method('getSourceFile')
            ->willReturn('/path/to/source/file.min.js');
        $this->asset->expects($this->atLeastOnce())
            ->method('getContentType')
            ->willReturn('js');
        $this->asset->expects($this->once())
            ->method('getPath')
            ->willReturn('/path/to/file.js');
        $this->asset->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);
        $this->bundleConfig->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with($context)
            ->willReturn($configView);
        $configView->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $configView->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn([]);
        $this->bundle->expects($this->once())
            ->method('addAsset')
            ->with($this->asset);

        $this->assertTrue($this->manager->addAsset($this->asset));
    }

    public function testFlush()
    {
        $this->bundle->expects($this->once())
            ->method('flush');
        $this->manager->flush();
    }
}
