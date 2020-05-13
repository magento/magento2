<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\Config\FileIterator;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Filesystem\File\ReadFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileIteratorTest extends TestCase
{
    /**
     * @var FileIterator
     */
    protected $fileIterator;

    /**
     * @var Read|MockObject
     */
    protected $fileRead;

    /**
     * Array of relative file paths
     *
     * @var array
     */
    protected $filePaths;

    /**
     * @var ReadFactory|MockObject
     */
    protected $fileReadFactory;

    protected function setUp(): void
    {
        $this->filePaths = ['/file1', '/file2'];
        $this->fileReadFactory = $this->createMock(ReadFactory::class);
        $this->fileRead = $this->createMock(Read::class);
        $this->fileIterator = new FileIterator($this->fileReadFactory, $this->filePaths);
    }

    protected function tearDown(): void
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
                ->willReturn($contents[$index++]);
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
                ->willReturn($contents[$index++]);
        }
        $this->assertEquals($expectedArray, $this->fileIterator->toArray());
    }
}
