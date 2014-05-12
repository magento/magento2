<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Email\Model\Template\Config;

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
     * @var \Magento\Framework\Filesystem\Directory\Read | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

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
        $this->filePaths = ['/file1', '/file2'];
        $this->directoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $this->moduleDirResolverMock = $this->getMock(
            'Magento\Framework\Module\Dir\ReverseResolver',
            [],
            [],
            '',
            false
        );

        $this->fileIterator = new FileIterator(
            $this->directoryMock,
            $this->filePaths,
            $this->moduleDirResolverMock
        );
    }

    protected function tearDown()
    {
        $this->fileIterator = null;
        $this->directoryMock = null;
        $this->filePaths = null;
        $this->moduleDirResolverMock = null;
    }

    public function testIterator()
    {
        $moduleName = 'Filesystem';
        $contents = ['<template 123>', '<template 321>'];
        $expectedResult = ['<template module="'. $moduleName .'" 123>', '<template module="'. $moduleName .'" 321>'];
        $directoryPath = 'directory/path';
        $index = 0;
        $dirIndex = 0;
        foreach ($this->filePaths as $filePath) {
            $this->directoryMock->expects($this->at($dirIndex++))
                ->method('getAbsolutePath')
                ->with($filePath)
                ->will($this->returnValue($directoryPath . $filePath));
            $this->moduleDirResolverMock->expects($this->at($index))
                ->method('getModuleName')
                ->with($directoryPath . $filePath)
                ->will($this->returnValue($moduleName));
            $this->directoryMock->expects($this->at($dirIndex++))
                ->method('readFile')
                ->with($filePath)
                ->will($this->returnValue($contents[$index++]));
        }
        $index = 0;
        foreach ($this->fileIterator as $fileContent) {
            $this->assertEquals($expectedResult[$index++], $fileContent);
        }
    }

    public function testIteratorNegative()
    {
        $directoryPath = 'directory/path';
        $filePath = $this->filePaths[0];

        $this->setExpectedException(
            'UnexpectedValueException',
            sprintf("Unable to determine a module, file '%s' belongs to.", $filePath)
        );

        $this->directoryMock->expects($this->at(0))
            ->method('getAbsolutePath')
            ->with($filePath)
            ->will($this->returnValue($directoryPath . $filePath));
        $this->moduleDirResolverMock->expects($this->at(0))
            ->method('getModuleName')
            ->with($directoryPath . $filePath)
            ->will($this->returnValue(false));
        $this->directoryMock->expects($this->never())
            ->method('readFile');

        $this->fileIterator->current();
    }
}
