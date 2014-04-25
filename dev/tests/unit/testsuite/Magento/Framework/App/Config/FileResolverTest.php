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
namespace Magento\Framework\App\Config;

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
     * @var \Magento\Framework\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
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
            array(),
            array('getPath'),
            '',
            false
        );
        $this->filesystem = $this->getMock(
            'Magento\Framework\App\Filesystem',
            array('getDirectoryRead'),
            array(),
            '',
            false
        );
        $this->moduleReader = $this->getMock(
            'Magento\Framework\Module\Dir\Reader',
            array(),
            array('getConfigurationFiles'),
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
        $directory = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Read',
            array('search', 'getRelativePath'),
            array(),
            '',
            false
        );
        $directory->expects(
            $this->once()
        )->method(
            'search'
        )->with(
            sprintf('{%1$s,*/%1$s}', $filename)
        )->will(
            $this->returnValue($fileList)
        );
        $this->filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->with(
            \Magento\Framework\App\Filesystem::CONFIG_DIR
        )->will(
            $this->returnValue($directory)
        );
        $this->iteratorFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $directory,
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
        return array(
            array('di.xml', array('di.xml', 'anotherfolder/di.xml')),
            array('no_files.xml', array()),
            array('one_file.xml', array('one_file.xml'))
        );
    }
}
