<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test of customization path model
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme\Customization;

use Magento\Framework\Component\ComponentRegistrar;

class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\Customization\Path
     */
    private $_model;

    /**
     * @var \Magento\Theme\Model\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_theme;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_directory;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrar;

    protected function setUp()
    {
        $this->_theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        /** @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystem */
        $filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->_directory = $this->getMock(\Magento\Framework\Filesystem\Directory\Read::class, [], [], '', false);
        $filesystem->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($this->_directory));
        $this->_directory->expects($this->any())->method('getAbsolutePath')->will($this->returnArgument(0));
        $this->componentRegistrar = $this->getMockForAbstractClass(
            \Magento\Framework\Component\ComponentRegistrarInterface::class
        );
        $this->_model = new \Magento\Framework\View\Design\Theme\Customization\Path(
            $filesystem,
            $this->componentRegistrar
        );
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
        $this->_theme->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue(123));
        $this->assertEquals($expectedPath, $this->_model->getCustomizationPath($this->_theme));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\Path::__construct
     * @covers \Magento\Framework\View\Design\Theme\Customization\Path::getCustomizationPath
     */
    public function testGetCustomizationPathNoId()
    {
        $this->_theme->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->assertNull($this->_model->getCustomizationPath($this->_theme));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\Path::getThemeFilesPath
     */
    public function testGetThemeFilesPath()
    {
        $this->_theme->expects($this->any())
            ->method('getFullPath')
            ->will($this->returnValue('frontend/Magento/theme'));
        $expectedPath = '/fill/theme/path';
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, 'frontend/Magento/theme')
            ->will($this->returnValue($expectedPath));
        $this->assertEquals($expectedPath, $this->_model->getThemeFilesPath($this->_theme));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\Path::getThemeFilesPath
     */
    public function testGetThemeFilesPathNoPath()
    {
        $this->_theme->expects($this->any())
            ->method('getFullPath')
            ->will($this->returnValue(null));
        $this->componentRegistrar->expects($this->never())
            ->method('getPath');
        $this->assertNull($this->_model->getCustomizationPath($this->_theme));
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
        $this->_theme->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue(123));
        $this->assertEquals($expectedPath, $this->_model->getCustomViewConfigPath($this->_theme));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\Path::getCustomViewConfigPath
     */
    public function testGetCustomViewConfigPathNoId()
    {
        $this->_theme->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->assertNull($this->_model->getCustomViewConfigPath($this->_theme));
    }
}
