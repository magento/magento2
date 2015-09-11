<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit;

use \Magento\Framework\Config\FileIterator;

/**
 * Class FileIteratorTest
 */
class FileIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileIterator
     */
    protected $fileIterator;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemDriver;

    /**
     * Array of relative file paths
     *
     * @var array
     */
    protected $filePaths;

    protected function setUp()
    {
        $this->filePaths = ['/file1', '/file2'];
        $this->filesystemDriver = $this->getMock('Magento\Framework\Filesystem\Driver\File', [], [], '', false);

        $this->fileIterator = new FileIterator(
            $this->filesystemDriver,
            $this->filePaths
        );
    }

    protected function tearDown()
    {
        $this->fileIterator = null;
        $this->directoryMock = null;
        $this->filePaths = null;
    }

    public function testIterator()
    {
        $contents = ['content1', 'content2'];
        $index = 0;
        foreach ($this->filePaths as $filePath) {
            $this->filesystemDriver->expects($this->at($index))
                ->method('fileGetContents')
                ->with($filePath)
                ->will($this->returnValue($contents[$index++]));
        }
        $index = 0;
        foreach ($this->fileIterator as $fileContent) {
            $this->assertEquals($contents[$index++], $fileContent);
        }
    }

    public function testToArray()
    {
        $contents = ['content1', 'content2'];
        $expectedArray = [];
        $index = 0;
        foreach ($this->filePaths as $filePath) {
            $expectedArray[$filePath] = $contents[$index];
            $this->filesystemDriver->expects($this->at($index))
                ->method('fileGetContents')
                ->with($filePath)
                ->will($this->returnValue($contents[$index++]));
        }
        $this->assertEquals($expectedArray, $this->fileIterator->toArray());
    }
}
