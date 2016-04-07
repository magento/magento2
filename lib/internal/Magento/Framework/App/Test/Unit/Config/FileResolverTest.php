<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Files resolver
     *
     * @var \Magento\Framework\App\Config\FileResolver
     */
    protected $model;

    /**
     * Filesystem
     *
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * File iterator factory
     *
     * @var \Magento\Framework\Config\FileIteratorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $iteratorFactory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleReader;

    protected function setUp()
    {
        $this->iteratorFactory = $this->getMock(
            'Magento\Framework\Config\FileIteratorFactory',
            [],
            ['getPath'],
            '',
            false
        );
        $this->filesystem = $this->getMock(
            'Magento\Framework\Filesystem',
            ['getDirectoryRead'],
            [],
            '',
            false
        );
        $this->moduleReader = $this->getMock(
            'Magento\Framework\Module\Dir\Reader',
            [],
            ['getConfigurationFiles'],
            '',
            false
        );
        $this->model = new \Magento\Framework\App\Config\FileResolver(
            $this->moduleReader,
            $this->filesystem,
            $this->iteratorFactory
        );
    }

    /**
     * Test for get method with primary scope
     *
     * @dataProvider providerGet
     * @param string $filename
     * @param array $fileList
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testGetPrimary($filename, $fileList)
    {
        $scope = 'primary';
        $directory = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $directory->expects(
            $this->once()
        )->method(
            'search'
        )->with(
            sprintf('{%1$s,*/%1$s}', $filename)
        )->will(
            $this->returnValue($fileList)
        );
        $i = 1;
        foreach ($fileList as $file) {
            $directory->expects($this->at($i++))->method('getAbsolutePath')->willReturn($file);
        }
        $this->filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->with(
            DirectoryList::CONFIG
        )->will(
            $this->returnValue($directory)
        );
        $this->iteratorFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $fileList
        )->will(
            $this->returnValue(true)
        );
        $this->assertTrue($this->model->get($filename, $scope));
    }

    /**
     * Test for get method with global scope
     *
     * @dataProvider providerGet
     * @param string $filename
     * @param array $fileList
     */
    public function testGetGlobal($filename, $fileList)
    {
        $scope = 'global';
        $this->moduleReader->expects(
            $this->once()
        )->method(
            'getConfigurationFiles'
        )->with(
            $filename
        )->will(
            $this->returnValue($fileList)
        );
        $this->assertEquals($fileList, $this->model->get($filename, $scope));
    }

    /**
     * Test for get method with default scope
     *
     * @dataProvider providerGet
     * @param string $filename
     * @param array $fileList
     */
    public function testGetDefault($filename, $fileList)
    {
        $scope = 'some_scope';
        $this->moduleReader->expects(
            $this->once()
        )->method(
            'getConfigurationFiles'
        )->with(
            $scope . '/' . $filename
        )->will(
            $this->returnValue($fileList)
        );
        $this->assertEquals($fileList, $this->model->get($filename, $scope));
    }

    /**
     * Data provider for get tests
     *
     * @return array
     */
    public function providerGet()
    {
        return [
            ['di.xml', ['di.xml', 'anotherfolder/di.xml']],
            ['no_files.xml', []],
            ['one_file.xml', ['one_file.xml']]
        ];
    }
}
