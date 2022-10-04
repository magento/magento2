<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Package\Processor\PreProcessor;

use Magento\Deploy\Console\DeployStaticOptions;
use Magento\Deploy\Package\Processor\PreProcessor\Css;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CssTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var Minification|MockObject
     */
    private $minification;

    /**
     * @var ReadInterface|MockObject
     */
    private $staticDir;

    /**
     * @var Package|MockObject
     */
    private $package;

    /**
     * @var PackageFile|MockObject
     */
    private $file;

    /**
     * @var Css
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->minification = $this->getMockBuilder(Minification::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->staticDir = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($this->staticDir);
        $this->package = $this->getMockBuilder(Package::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->file = $this->getMockBuilder(PackageFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Css($this->filesystem, $this->minification);
    }

    public function testProcessWithNonExistingFile(): void
    {
        $options = [
            DeployStaticOptions::NO_CSS => false
        ];
        $nonReadableFile = 'nonReadableFile';

        $this->package->expects($this->once())
            ->method('getParentFiles')
            ->with('css')
            ->willReturn([$this->file]);
        $this->file->expects($this->once())
            ->method('getPackage')
            ->willReturn($this->package);
        $this->file->expects($this->any())
            ->method('getDeployedFilePath')
            ->willReturn('some/path/to/file');
        $this->package->expects($this->any())
            ->method('getFiles')
            ->willReturn([]);
        $this->minification->expects($this->once())
            ->method('addMinifiedSign')
            ->willReturn($nonReadableFile);
        $this->staticDir->expects($this->once())
            ->method('isReadable')
            ->with($nonReadableFile)
            ->willReturn(false);
        $this->staticDir->expects($this->never())
            ->method('readFile');
        $this->model->process($this->package, $options);
    }
}
