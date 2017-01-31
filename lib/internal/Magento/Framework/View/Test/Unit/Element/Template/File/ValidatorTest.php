<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Template\File;

use \Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
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
    private $_validator;

    /**
     * Mock for view file system
     *
     * @var \Magento\Framework\FileSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_fileSystemMock;

    /**
     * Mock for scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_scopeConfigMock;

    /**
     * Mock for root directory reader
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rootDirectoryMock;

    /**
     * Mock for compiled directory reader
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $compiledDirectoryMock;

    /**
     * @var ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrar;

    /**
     * Test Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_fileSystemMock = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->_scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->rootDirectoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->compiledDirectoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface');

        $this->_fileSystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValueMap(
                [
                    [DirectoryList::ROOT, DriverPool::FILE, $this->rootDirectoryMock],
                    [DirectoryList::TEMPLATE_MINIFICATION_DIR, DriverPool::FILE, $this->compiledDirectoryMock],
                ]
            ));

        $this->compiledDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValue('/magento/var/compiled'));

        $this->componentRegistrar = $this->getMock('Magento\Framework\Component\ComponentRegistrar', [], [], '', false);
        $this->componentRegistrar->expects($this->any())
            ->method('getPaths')
            ->will(
                $this->returnValueMap(
                    [
                        [ComponentRegistrar::MODULE, ['/magento/app/code/Some/Module']],
                        [ComponentRegistrar::THEME, ['/magento/themes/default']]
                    ]
                )
            );
        $this->_validator = new \Magento\Framework\View\Element\Template\File\Validator(
            $this->_fileSystemMock,
            $this->_scopeConfigMock,
            $this->componentRegistrar
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
