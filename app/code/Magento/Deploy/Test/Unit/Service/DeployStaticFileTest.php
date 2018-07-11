<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Service\DeployStaticFile;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\PreProcessor\FileNameResolver;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use PHPUnit_Framework_MockObject_Matcher_InvokedCount as InvokedCount;

class DeployStaticFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeployStaticFile
     */
    private $service;

    /**
     * @var FileNameResolver | Mock
     */
    private $fileNameResolver;

    /**
     * @var Repository | Mock
     */
    private $assetRepo;

    /**
     * @var Publisher | Mock
     */
    private $assetPublisher;

    /**
     * @var Filesystem | Mock
     */
    private $filesystem;

    /**
     * @var Minification | Mock
     */
    private $minification;

    /**
     * @var DriverInterface | Mock
     */
    private $fsDriver;

    protected function setUp()
    {
        $this->fileNameResolver = $this->createPartialMock(FileNameResolver::class, ['resolve']);
        $this->assetRepo = $this->createPartialMock(Repository::class, ['createAsset']);
        $this->assetPublisher = $this->createPartialMock(Publisher::class, ['publish']);
        $this->filesystem = $this->createPartialMock(Filesystem::class, ['getDirectoryWrite']);
        $this->minification = $this->createMock(Minification::class);
        $this->fsDriver = $this->getMockForAbstractClass(DriverInterface::class);

        $directory = $this->createMock(WriteInterface::class);

        $directory->method('isExist')
            ->willReturn(true);

        $directory->method('getAbsolutePath')
            ->willReturn('path');

        $directory->method('getDriver')
            ->willReturn($this->fsDriver);

        $this->filesystem
            ->method('getDirectoryWrite')
            ->willReturn($directory);

        $this->service = new DeployStaticFile(
            $this->filesystem,
            $this->assetRepo,
            $this->assetPublisher,
            $this->fileNameResolver,
            $this->minification
        );
    }

    /**
     * @param string $fileName
     * @param array $params
     * @param InvokedCount $callDelete
     * @dataProvider deployFileDataProvider
     */
    public function testDeployFile($fileName, array $params, InvokedCount $callDelete)
    {
        $file = $this->createMock(File::class);
        $file->method('getPath')
            ->willReturn($fileName);

        $this->fileNameResolver
            ->method('resolve')
            ->with($fileName)
            ->willReturn($fileName);

        $this->assetRepo
            ->method('createAsset')
            ->willReturn($file);

        $this->fsDriver
            ->method('isFile')
            ->with('path')
            ->willReturn(true);

        $this->fsDriver
            ->expects($callDelete)
            ->method('deleteFile');

        $this->service->deployFile($fileName, $params);
    }

    /**
     * List of options for the file deploy.
     *
     * @return array
     */
    public function deployFileDataProvider(): array
    {
        return [
            [
                'file1',
                ['replace' => 'any value',],
                self::once()
            ],
            [
                'file1',
                ['replace' => false,],
                self::once()
            ],
            [
                'file1',
                [],
                self::never()
            ],
        ];
    }
}
