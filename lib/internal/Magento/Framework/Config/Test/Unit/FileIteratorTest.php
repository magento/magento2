<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\Framework\Filesystem\File\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileRead;

    /**
     * Array of relative file paths
     *
     * @var array
     */
    protected $filePaths;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileReadFactory;

    protected function setUp()
    {
        $this->filePaths = ['/file1', '/file2'];
        $this->fileReadFactory = $this->getMock('Magento\Framework\Filesystem\File\ReadFactory', [], [], '', false);
        $this->fileRead = $this->getMock('Magento\Framework\Filesystem\File\Read', [], [], '', false);
        $this->fileIterator = new FileIterator($this->fileReadFactory, $this->filePaths);
    }

    protected function tearDown()
    {
        $this->fileIterator = null;
        $this->filePaths = null;
    }

    public function testIterator()
    {
        $contents = ['content1', 'content2'];
        $index = 0;
        foreach ($this->filePaths as $filePath) {
            $this->fileReadFactory->expects($this->at($index))
                ->method('create')
                ->with($filePath)
                ->willReturn($this->fileRead);
            $this->fileRead->expects($this->at($index))
                ->method('readAll')
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
            $this->fileReadFactory->expects($this->at($index))
                ->method('create')
                ->with($filePath)
                ->willReturn($this->fileRead);
            $this->fileRead->expects($this->at($index))
                ->method('readAll')
                ->will($this->returnValue($contents[$index++]));
        }
        $this->assertEquals($expectedArray, $this->fileIterator->toArray());
    }
}
