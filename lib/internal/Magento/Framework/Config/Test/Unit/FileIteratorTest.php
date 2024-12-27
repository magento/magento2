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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->filePaths = ['/file1', '/file2'];
        $this->fileReadFactory = $this->createMock(ReadFactory::class);
        $this->fileRead = $this->createMock(Read::class);
        $this->fileIterator = new FileIterator($this->fileReadFactory, $this->filePaths);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->fileIterator = null;
        $this->filePaths = null;
    }

    /**
     * @return void
     */
    public function testIterator(): void
    {
        $contents = ['content1', 'content2'];
        $createWithArgs = $createWillReturnArgs = $readAllWillReturnArgs = [];
        $index = 0;

        foreach ($this->filePaths as $filePath) {
            $createWithArgs[] = [$filePath];
            $createWillReturnArgs[] = $this->fileRead;
            $readAllWillReturnArgs[] = $contents[$index++];
        }
        $this->fileReadFactory
            ->method('create')
            ->willReturnCallback(function ($createWithArgs) use ($createWillReturnArgs) {
                if (!empty($createWithArgs)) {
                    static $callCount = 0;
                    $returnValue = $createWillReturnArgs[$callCount] ?? null;
                    $callCount++;
                    return $returnValue;
                }
            });
        $this->fileRead
            ->method('readAll')
            ->willReturnOnConsecutiveCalls(...$readAllWillReturnArgs);
        $index = 0;

        foreach ($this->fileIterator as $fileContent) {
            $this->assertEquals($contents[$index++], $fileContent);
        }
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $contents = ['content1', 'content2'];
        $expectedArray = [];
        $createWithArgs = $createWillReturnArgs = $readAllWillReturnArgs = [];
        $index = 0;
        foreach ($this->filePaths as $filePath) {
            $expectedArray[$filePath] = $contents[$index];
            $createWithArgs[] = [$filePath];
            $createWillReturnArgs[] = $this->fileRead;
            $readAllWillReturnArgs[] = $contents[$index++];
        }
        $this->fileReadFactory
            ->method('create')
            ->willReturnCallback(function ($createWithArgs) use ($createWillReturnArgs) {
                if (!empty($createWithArgs)) {
                    static $callCount = 0;
                    $returnValue = $createWillReturnArgs[$callCount] ?? null;
                    $callCount++;
                    return $returnValue;
                }
            });
        $this->fileRead
            ->method('readAll')
            ->willReturnOnConsecutiveCalls(...$readAllWillReturnArgs);

        $this->assertEquals($expectedArray, $this->fileIterator->toArray());
    }
}
