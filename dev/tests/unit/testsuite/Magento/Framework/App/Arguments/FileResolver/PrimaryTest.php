<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Arguments\FileResolver;

use Magento\Framework\App\Filesystem\DirectoryList;

class PrimaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $fileList
     * @param string $scope
     * @param string $filename
     * @dataProvider getMethodDataProvider
     */
    public function testGet(array $fileList, $scope, $filename)
    {
        $directory = $this->getMock('Magento\Framework\Filesystem\Directory\Read', ['search'], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', ['getDirectoryRead'], [], '', false);
        $iteratorFactory = $this->getMock(
            'Magento\Framework\Config\FileIteratorFactory',
            ['create'],
            [],
            '',
            false
        );

        $filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->with(
            DirectoryList::CONFIG
        )->will(
            $this->returnValue($directory)
        );

        $directory->expects($this->once())->method('search')->will($this->returnValue($fileList));

        $iteratorFactory->expects($this->once())->method('create')->will($this->returnValue(true));

        $model = new \Magento\Framework\App\Arguments\FileResolver\Primary($filesystem, $iteratorFactory);

        $this->assertTrue($model->get($filename, $scope));
    }

    /**
     * @return array
     */
    public function getMethodDataProvider()
    {
        return [[['config/di.xml', 'config/some_config/di.xml'], 'primary', 'di.xml']];
    }
}
