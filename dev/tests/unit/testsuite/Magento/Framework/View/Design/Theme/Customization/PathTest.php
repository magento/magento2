<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test of customization path model
 */
namespace Magento\Framework\View\Design\Theme\Customization;

class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\Customization\Path
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_theme;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_directory;

    protected function setUp()
    {
        $this->_theme = $this->getMock('Magento\Core\Model\Theme', ['__wakeup'], [], '', false);
        $this->_appState = $this->getMock('Magento\Framework\App\State', ['getAreaCode'], [], '', false);
        $appStateProperty = new \ReflectionProperty('\Magento\Core\Model\Theme', '_appState');
        $appStateProperty->setAccessible(true);
        $appStateProperty->setValue($this->_theme, $this->_appState);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->_directory = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $filesystem->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($this->_directory));
        $this->_directory->expects($this->once())->method('getAbsolutePath')->will($this->returnArgument(0));
        $this->_model = new \Magento\Framework\View\Design\Theme\Customization\Path($filesystem);
    }

    protected function tearDown()
    {
        $this->_theme = null;
        $this->_directory = null;
        $this->_model = null;
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\Path::__construct
     * @covers \Magento\Framework\View\Design\Theme\Customization\Path::getCustomizationPath
     */
    public function testGetCustomizationPath()
    {
        $expectedPath = implode('/', [\Magento\Framework\View\Design\Theme\Customization\Path::DIR_NAME, '123']);
        $this->assertEquals($expectedPath, $this->_model->getCustomizationPath($this->_theme->setId(123)));
        $this->assertNull($this->_model->getCustomizationPath($this->_theme->setId(null)));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\Path::getThemeFilesPath
     */
    public function testGetThemeFilesPath()
    {
        $this->_appState->expects($this->any())->method('getAreaCode')->will($this->returnValue('area51'));
        $expectedPath = implode('/', ['area51', 'path']);
        $this->assertEquals($expectedPath, $this->_model->getThemeFilesPath($this->_theme->setThemePath('path')));
        $this->assertNull($this->_model->getCustomizationPath($this->_theme->setThemePath(null)));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\Path::getCustomViewConfigPath
     */
    public function testGetCustomViewConfigPath()
    {
        $expectedPath = implode(
            '/',
            [
                \Magento\Framework\View\Design\Theme\Customization\Path::DIR_NAME,
                '123',
                \Magento\Framework\View\ConfigInterface::CONFIG_FILE_NAME
            ]
        );
        $this->assertEquals($expectedPath, $this->_model->getCustomViewConfigPath($this->_theme->setId(123)));
        $this->assertNull($this->_model->getCustomViewConfigPath($this->_theme->setId(null)));
    }
}
