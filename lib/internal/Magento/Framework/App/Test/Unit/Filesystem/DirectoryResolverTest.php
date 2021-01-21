<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Filesystem;

/**
 * Unit tests for the \Magento\Framework\App\Filesystem\DirectoryResolver class.
 */
class DirectoryResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit\Framework\MockObject\MockObject
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryResolver
     */
    private $directoryResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->directoryList = $this->getMockBuilder(\Magento\Framework\App\Filesystem\DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryResolver = new \Magento\Framework\App\Filesystem\DirectoryResolver(
            $this->directoryList,
            $this->filesystem
        );
    }

    /**
     * @dataProvider validatePathDataProvider
     * @param string $path
     * @param bool $expectedResult
     * @return void
     */
    public function testValidatePath(string $path, bool $expectedResult): void
    {
        $rootPath = '/path/root';
        $directoryConfig = 'directory_config';
        $directory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->setMethods(['getDriver'])
            ->getMockForAbstractClass();
        $driver = $this->getMockBuilder(\Magento\Framework\Filesystem\DriverInterface::class)
            ->setMethods(['getRealPathSafety'])
            ->getMockForAbstractClass();
        $directory->expects($this->atLeastOnce())->method('getDriver')->willReturn($driver);
        $driver->expects($this->atLeastOnce())->method('getRealPathSafety')->with($path)
            ->willReturnArgument(0);
        $this->filesystem->expects($this->atLeastOnce())->method('getDirectoryWrite')->with($directoryConfig)
            ->willReturn($directory);
        $this->directoryList->expects($this->atLeastOnce())->method('getPath')->with($directoryConfig)
            ->willReturn($rootPath);
        $this->assertEquals($expectedResult, $this->directoryResolver->validatePath($path, $directoryConfig));
    }

    /**
     * @return array
     */
    public function validatePathDataProvider()
    {
        return [
            ['/path/root/for/validation', true],
            ['/path/invalid/for/validation', false],
        ];
    }
}
