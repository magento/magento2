<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset\Publisher;


class SymlinkTest extends \PHPUnit_Framework_TestCase
{
    public function testPublishFile()
    {
        $rootDir = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteInterface')
            ->getMock();
        $targetDir = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteInterface')
            ->getMock();
        $sourcePath = 'source/path/file';
        $destinationPath = 'destination/path/file';

        $copyPublisher = new Symlink;
        $rootDir->expects($this->once())
            ->method('createSymlink')
            ->with(
                $sourcePath,
                $destinationPath,
                $targetDir
            )->willReturn(true);

        $this->assertTrue($copyPublisher->publishFile($rootDir, $targetDir, $sourcePath, $destinationPath));
    }
}