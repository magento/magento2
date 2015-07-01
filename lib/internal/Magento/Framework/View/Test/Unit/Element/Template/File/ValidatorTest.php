<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Template\File;

use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Filesystem\DriverPool;

/**
 * Class ValidatorTest
 * @package Magento\Framework\View\Test\Unit\Element\Template\File
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Resolver object
     *
     * @var \Magento\Framework\View\Element\Template\File\Validator
     */
    protected $_validator;

    /**
     * Mock for view file system
     *
     * @var \Magento\Framework\FileSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileSystemMock;

    /**
     * Mock for scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * Mock for root directory reader
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectoryMock;

    /**
     * Mock for app directory reader
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appDirectoryMock;

    /**
     * Mock for themes directory reader
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themesDirectoryMock;

    /**
     * Mock for compiled directory reader
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $compiledDirectoryMock;

    /**
     * Test Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->_fileSystemMock = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->_scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->rootDirectoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->appDirectoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->themesDirectoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->compiledDirectoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface');

        $this->_fileSystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValueMap(
                [
                    [DirectoryList::ROOT, DriverPool::FILE, $this->rootDirectoryMock],
                    [DirectoryList::THEMES, DriverPool::FILE, $this->themesDirectoryMock],
                    [DirectoryList::APP, DriverPool::FILE, $this->appDirectoryMock],
                    [DirectoryList::TEMPLATE_MINIFICATION_DIR, DriverPool::FILE, $this->compiledDirectoryMock],
                ]
            ));

        $this->compiledDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValue('/magento/var/compiled'));

        $this->appDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValue('/magento/app'));

        $this->themesDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValue('/magento/themes'));

        $this->_validator = new \Magento\Framework\View\Element\Template\File\Validator(
            $this->_fileSystemMock,
            $this->_scopeConfigMock
        );
    }

    /**
     * Test is file valid
     *
     * @param string $file
     * @param bool $expectedResult
     *
     * @dataProvider testIsValidDataProvider
     *
     * @return void
     */
    public function testIsValid($file, $expectedResult)
    {

        $this->rootDirectoryMock->expects($this->any())->method('isFile')->will($this->returnValue(true));
        $this->assertEquals($expectedResult, $this->_validator->isValid($file));
    }

    /**
     * Data provider for testIsValid
     *
     * @return []
     */
    public function testIsValidDataProvider()
    {
        return [
            'empty' => ['', false],
            '/magento/var/compiled/template.phtml' => ['/magento/var/compiled/template.phtml', true],
            '/magento/themes/default/template.phtml' => ['/magento/themes/default/template.phtml', true],
            '/magento/app/code/Some/Module/template.phtml' => ['/magento/app/code/Some/Module/template.phtml', true],
            '/magento/x' => ['/magento/x', false],
        ];
    }
}
