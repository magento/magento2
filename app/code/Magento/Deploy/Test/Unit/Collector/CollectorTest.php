<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Collector;

use Magento\Deploy\Collector\Collector;
use Magento\Deploy\Source\SourcePool;
use Magento\Deploy\Package\PackageFactory;
use Magento\Deploy\Source\SourceInterface;
use Magento\Deploy\Package\PackageFile;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Asset\PreProcessor\FileNameResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectorTest extends TestCase
{
    /**
     * @var SourcePool|MockObject
     */
    private $sourcePool;

    /**
     * @var FileNameResolver|MockObject
     */
    private $fileNameResolver;

    /**
     * @var PackageFactory|MockObject
     */
    private $packageFactory;

    /**
     * @var Manager|MockObject
     */
    private $moduleManager;

    /**
     * @var SourceInterface|MockObject
     */
    private $source;

    /**
     * @var PackageFile|MockObject
     */
    private $fileWithName;

    /**
     * @var PackageFile|MockObject
     */
    private $fileWithoutName;

    /**
     * @var Collector
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sourcePool = $this->getMockBuilder(SourcePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileNameResolver = $this->getMockBuilder(FileNameResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->packageFactory = $this->getMockBuilder(PackageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleManager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->source = $this->getMockBuilder(SourceInterface::class)
            ->getMockForAbstractClass();
        $this->fileWithName = $this->getMockBuilder(PackageFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileWithName->expects($this->any())
            ->method('getFileName')
            ->willReturn('name');
        $this->fileWithoutName = $this->getMockBuilder(PackageFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileWithoutName->expects($this->any())
            ->method('getFileName')
            ->willReturn('');

        $this->model = new Collector(
            $this->sourcePool,
            $this->fileNameResolver,
            $this->packageFactory,
            $this->moduleManager
        );
    }

    public function testCollect(): void
    {
        $this->sourcePool->expects($this->once())
            ->method('getAll')
            ->willReturn([$this->source]);
        $this->source->expects($this->once())
            ->method('get')
            ->willReturn([$this->fileWithoutName, $this->fileWithName]);
        $this->fileWithoutName->expects($this->exactly(0))
            ->method('setDeployedFileName');
        $this->fileWithName->expects($this->exactly(1))
            ->method('setDeployedFileName');
        $this->model->collect();
    }
}
