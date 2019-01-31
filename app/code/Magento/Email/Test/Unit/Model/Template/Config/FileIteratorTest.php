<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template\Config;

use Magento\Email\Model\Template\Config\FileIterator;

/**
 * Class FileIteratorTest
 */
class FileIteratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FileIterator
     */
    protected $fileIterator;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileReadFactory;

    /**
     * @var \Magento\Framework\Filesystem\File\Read | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileRead;

    /**
     * @var \Magento\Framework\Module\Dir\ReverseResolver | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleDirResolverMock;

    /**
     * Array of relative file paths
     *
     * @var array
     */
    protected $filePaths;

    protected function setUp()
    {
        $this->filePaths = ['directory/path/file1', 'directory/path/file2'];
        $this->fileReadFactory = $this->createMock(\Magento\Framework\Filesystem\File\ReadFactory::class);
        $this->fileRead = $this->createMock(\Magento\Framework\Filesystem\File\Read::class);
        $this->moduleDirResolverMock = $this->createMock(\Magento\Framework\Module\Dir\ReverseResolver::class);

        $this->fileIterator = new \Magento\Email\Model\Template\Config\FileIterator(
            $this->fileReadFactory,
            $this->filePaths,
            $this->moduleDirResolverMock
        );
    }

    protected function tearDown()
    {
        $this->fileIterator = null;
        $this->filePaths = null;
        $this->moduleDirResolverMock = null;
    }

    public function testIterator()
    {
        $moduleName = 'Filesystem';
        $contents = ['<template 123>', '<template 321>'];
        $expectedResult = [
            '<template module="' . $moduleName . '" 123>',
            '<template module="' . $moduleName . '" 321>'
        ];
        $index = 0;
        $dirIndex = 0;
        foreach ($this->filePaths as $filePath) {
            $this->moduleDirResolverMock->expects($this->at($index))
                ->method('getModuleName')
                ->with($filePath)
                ->will($this->returnValue($moduleName));
            $this->fileReadFactory->expects($this->at($dirIndex))
                ->method('create')
                ->with($filePath)
                ->willReturn($this->fileRead);
            $this->fileRead->expects($this->at($dirIndex++))
                ->method('readAll')
                ->will($this->returnValue($contents[$index++]));
        }
        $index = 0;
        foreach ($this->fileIterator as $fileContent) {
            $this->assertEquals($expectedResult[$index++], $fileContent);
        }
    }

    public function testIteratorNegative()
    {
        $filePath = $this->filePaths[0];

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage(sprintf("Unable to determine a module, file '%s' belongs to.", $filePath));

        $this->moduleDirResolverMock->expects($this->at(0))
            ->method('getModuleName')
            ->with($filePath)
            ->will($this->returnValue(false));
        $this->fileReadFactory->expects($this->never())->method('create');
        $this->fileRead->expects($this->never())->method('readAll');

        $this->fileIterator->current();
    }
}
