<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\View\Asset\MaterializationStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\View\Asset\MaterializationStrategy\Symlink;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset\LocalInterface;

use PHPUnit\Framework\TestCase;

class SymlinkTest extends TestCase
{
    /**
     * @var Symlink
     */
    private $symlinkPublisher;

    protected function setUp(): void
    {
        $this->symlinkPublisher = new Symlink();
    }

    public function testPublishFile()
    {
        $rootDir = $this->getMockBuilder(WriteInterface::class)
            ->getMock();
        $targetDir = $this->getMockBuilder(WriteInterface::class)
            ->getMock();
        $sourcePath = 'source/path/file';
        $destinationPath = 'destination/path/file';

        $rootDir->expects($this->once())
            ->method('createSymlink')
            ->with(
                $sourcePath,
                $destinationPath,
                $targetDir
            )->willReturn(true);

        $this->assertTrue($this->symlinkPublisher->publishFile($rootDir, $targetDir, $sourcePath, $destinationPath));
    }

    /**
     * @dataProvider sourceFileDataProvider
     */
    public function testIsSupported($path, $expectation)
    {
        $asset = $this->getMockBuilder(LocalInterface::class)
            ->setMethods([])
            ->getMockForAbstractClass();
        $asset->expects($this->once())
            ->method('getSourceFile')
            ->willReturn($path);
        $this->assertEquals($expectation, $this->symlinkPublisher->isSupported($asset));
    }

    /**
     * @return array
     */
    public function sourceFileDataProvider()
    {
        return [
            ['path/to/file', true],
            [DirectoryList::TMP_MATERIALIZATION_DIR . '/path/to/file', false]
        ];
    }
}
