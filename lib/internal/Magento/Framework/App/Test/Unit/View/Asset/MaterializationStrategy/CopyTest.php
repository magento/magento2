<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\View\Asset\MaterializationStrategy;

use \Magento\Framework\App\View\Asset\MaterializationStrategy\Copy;

class CopyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Copy
     */
    private $copyPublisher;

    protected function setUp()
    {
        $this->copyPublisher = new Copy;
    }

    public function testPublishFile()
    {
        $rootDir = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteInterface')
            ->getMock();
        $targetDir = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteInterface')
            ->getMock();
        $sourcePath = 'source/path/file';
        $destinationPath = 'destination/path/file';

        $rootDir->expects($this->once())
            ->method('copyFile')
            ->with(
                $sourcePath,
                $destinationPath,
                $targetDir
            )->willReturn(true);

        $this->assertTrue($this->copyPublisher->publishFile($rootDir, $targetDir, $sourcePath, $destinationPath));
    }

    public function testIsSupported()
    {
        $asset = $this->getMockBuilder('Magento\Framework\View\Asset\LocalInterface')
            ->getMock();
        $this->assertTrue($this->copyPublisher->isSupported($asset));
    }
}
