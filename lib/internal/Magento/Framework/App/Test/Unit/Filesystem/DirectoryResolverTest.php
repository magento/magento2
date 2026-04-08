<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Filesystem;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem\DirectoryResolver;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the \Magento\Framework\App\Filesystem\DirectoryResolver class.
 */
class DirectoryResolverTest extends TestCase
{
    /**
     * @var DirectoryList|MockObject
     */
    private $directoryList;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var DirectoryResolver
     */
    private $directoryResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->directoryList = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryResolver = new DirectoryResolver(
            $this->directoryList,
            $this->filesystem
        );
    }

    /**
     * @param string $path
     * @param bool $expectedResult
     * @return void
     */
    #[DataProvider('validatePathDataProvider')]
    public function testValidatePath(string $path, bool $expectedResult): void
    {
        $rootPath = '/path/root';
        $directoryConfig = 'directory_config';
        $directory = $this->createStub(WriteInterface::class);
        $driver = $this->createStub(DriverInterface::class);
        $directory->method('getDriver')->willReturn($driver);
        $driver->method('getRealPathSafety')->with($path)->willReturnArgument(0);
        $this->filesystem->expects($this->atLeastOnce())->method('getDirectoryWrite')->with($directoryConfig)
            ->willReturn($directory);
        $directory->method('getAbsolutePath')->willReturn($rootPath);
        $this->assertEquals($expectedResult, $this->directoryResolver->validatePath($path, $directoryConfig));
    }

    /**
     * @return array
     */
    public static function validatePathDataProvider()
    {
        return [
            ['/path/root/for/validation', true],
            ['/path/invalid/for/validation', false],
        ];
    }
}
