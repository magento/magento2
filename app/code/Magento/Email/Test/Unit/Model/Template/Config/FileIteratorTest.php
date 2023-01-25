<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Template\Config;

use Magento\Email\Model\Template\Config\FileIterator;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Module\Dir\ReverseResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileIteratorTest extends TestCase
{
    /**
     * @var FileIterator
     */
    protected $fileIterator;

    /**
     * @var ReadFactory|MockObject
     */
    protected $fileReadFactory;

    /**
     * @var Read|MockObject
     */
    protected $fileRead;

    /**
     * @var ReverseResolver|MockObject
     */
    protected $moduleDirResolverMock;

    /**
     * Array of relative paths
     *
     * @var array
     */
    protected $filePaths;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->filePaths = ['directory/path/file1', 'directory/path/file2'];
        $this->fileReadFactory = $this->createMock(ReadFactory::class);
        $this->fileRead = $this->createMock(Read::class);
        $this->moduleDirResolverMock = $this->createMock(ReverseResolver::class);

        $this->fileIterator = new FileIterator(
            $this->fileReadFactory,
            $this->filePaths,
            $this->moduleDirResolverMock
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->fileIterator = null;
        $this->filePaths = null;
        $this->moduleDirResolverMock = null;
    }

    /**
     * @return void
     */
    public function testIterator(): void
    {
        $moduleName = 'Filesystem';
        $contents = ['<template 123>', '<template 321>'];
        $expectedResult = [
            '<template module="' . $moduleName . '" 123>',
            '<template module="' . $moduleName . '" 321>'
        ];
        $index = 0;

        $moduleDirResolverWithArgs = $moduleDirResolverWillReturnArgs = [];
        $fileReadFactoryWithArgs = $fileReadFactoryWillReturnArgs = [];
        $fileReadWillReturnArgs = [];

        foreach ($this->filePaths as $filePath) {
            $moduleDirResolverWithArgs[] = [$filePath];
            $moduleDirResolverWillReturnArgs[] = $moduleName;

            $fileReadFactoryWithArgs[] = [$filePath];
            $fileReadFactoryWillReturnArgs[] = $this->fileRead;

            $fileReadWillReturnArgs[] = $contents[$index++];
        }
        $this->moduleDirResolverMock
            ->method('getModuleName')
            ->withConsecutive(...$moduleDirResolverWithArgs)
            ->willReturnOnConsecutiveCalls(...$moduleDirResolverWillReturnArgs);
        $this->fileReadFactory
            ->method('create')
            ->withConsecutive(...$fileReadFactoryWithArgs)
            ->willReturnOnConsecutiveCalls(...$fileReadFactoryWillReturnArgs);
        $this->fileRead
            ->method('readAll')
            ->willReturnOnConsecutiveCalls(...$fileReadWillReturnArgs);

        $index = 0;
        foreach ($this->fileIterator as $fileContent) {
            $this->assertEquals($expectedResult[$index++], $fileContent);
        }
    }

    /**
     * @return void
     */
    public function testIteratorNegative(): void
    {
        $filePath = $this->filePaths[0];

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage(sprintf("Unable to determine a module, file '%s' belongs to.", $filePath));

        $this->moduleDirResolverMock
            ->method('getModuleName')
            ->with($filePath)
            ->willReturn(false);
        $this->fileReadFactory->expects($this->never())->method('create');
        $this->fileRead->expects($this->never())->method('readAll');

        $this->fileIterator->current();
    }
}
