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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test of customization path model
 */
namespace Magento\Core\Model\Theme\Customization;

class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Theme\Customization\Path
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

    protected function setUp()
    {
        $this->_theme = $this->getMock('Magento\Core\Model\Theme', null, array(), '', false);
        $this->_dir = $this->getMock('Magento\App\Dir', array(), array(), '', false);
        $this->_model = new \Magento\Core\Model\Theme\Customization\Path($this->_dir);
    }

    protected function tearDown()
    {
        $this->_theme = null;
        $this->_dir = null;
        $this->_model = null;
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization\Path::__construct
     * @covers \Magento\Core\Model\Theme\Customization\Path::getCustomizationPath
     */
    public function testGetCustomizationPath()
    {
        $this->_dir->expects($this->once())->method('getDir')->with(\Magento\App\Dir::MEDIA)
            ->will($this->returnValue('/media_dir'));
        $expectedPath = implode(
            DIRECTORY_SEPARATOR,
            array('/media_dir', \Magento\Core\Model\Theme\Customization\Path::DIR_NAME, '123')
        );
        $this->assertEquals($expectedPath, $this->_model->getCustomizationPath($this->_theme->setId(123)));
        $this->assertNull($this->_model->getCustomizationPath($this->_theme->setId(null)));
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization\Path::getThemeFilesPath
     */
    public function testGetThemeFilesPath()
    {
        $this->_theme->setArea('area51');
        $this->_dir->expects($this->once())->method('getDir')->with(\Magento\App\Dir::THEMES)
            ->will($this->returnValue('/themes_dir'));
        $expectedPath = implode(
            \Magento\Filesystem::DIRECTORY_SEPARATOR,
            array('/themes_dir', 'area51', 'path')
        );
        $this->assertEquals(
            \Magento\Filesystem::fixSeparator($expectedPath),
            \Magento\Filesystem::fixSeparator($this->_model->getThemeFilesPath($this->_theme->setThemePath('path')))
        );
        $this->assertNull($this->_model->getCustomizationPath($this->_theme->setThemePath(null)));
    }

    /**
     * @covers \Magento\Core\Model\Theme\Customization\Path::getCustomViewConfigPath
     */
    public function testGetCustomViewConfigPath()
    {
        $this->_dir->expects($this->once())->method('getDir')->with(\Magento\App\Dir::MEDIA)
            ->will($this->returnValue('/media_dir'));
        $expectedPath = implode(
            DIRECTORY_SEPARATOR,
            array('/media_dir', \Magento\Core\Model\Theme\Customization\Path::DIR_NAME, '123',
                \Magento\Core\Model\Theme::FILENAME_VIEW_CONFIG)
        );
        $this->assertEquals($expectedPath, $this->_model->getCustomViewConfigPath($this->_theme->setId(123)));
        $this->assertNull($this->_model->getCustomViewConfigPath($this->_theme->setId(null)));
    }
}
