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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test that Design Package delegates fallback resolution to a Fallback model
 */
class Mage_Core_Model_Design_FallbackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getFileDataProvider
     * @param Mage_Core_Model_Theme|PHPUnit_Framework_MockObject_MockObject $theme
     * @param string $file
     * @param string $targetFile
     * @param string $expectedFileName
     * @cover Mage_Core_Model_Design_Fallback::_fallback()
     */
    public function testGetFile($theme, $file, $targetFile, $expectedFileName)
    {
        $designDir = 'design_dir';
        $moduleDir = 'module_view_dir';
        $module = 'Mage_Core11';

        $filesystem = $this->_getFileSystemMock($targetFile);
        $objectManager = $this->_getObjectManagerMock();
        $dirs = $this->_getDirsMock();

        $configModel = $this->getMock('Mage_Core_Model_Config', array('getModuleDir'), array(), '', false);

        $configModel->expects($this->any())
            ->method('getModuleDir')
            ->will($this->returnValue($moduleDir));

        $objectManager->expects($this->any())
            ->method('get')
            ->with('Mage_Core_Model_Config')
            ->will($this->returnValue($configModel));

        $dirs->expects($this->any())
            ->method('getDir')
            ->with(Mage_Core_Model_Dir::THEMES)
            ->will($this->returnValue($designDir));

        $data = array(
            'area'       => 'area51',
            'locale'     => 'en_EN',
            'themeModel' => $theme,
        );

        $fallback = new Mage_Core_Model_Design_Fallback($dirs, $objectManager, $filesystem, $data);
        $filename = $fallback->getFile($file, $module);

        $this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $expectedFileName), $filename);
    }

    /**
     * @return array
     */
    public function getFileDataProvider()
    {
        $file = 'test.txt';
        $customizationPath = 'custom';
        $themePath = 'theme_path';
        $parentThemePath = 'parent_theme_path';

        /** @var $parentTheme Mage_Core_Model_Theme */
        $parentTheme = $this->getMock('Mage_Core_Model_Theme', array('getThemePath'), array(), '', false);
        $parentTheme->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($parentThemePath));

        /** @var $themeSimple Mage_Core_Model_Theme */
        $themeSimple = $this->getMock('Mage_Core_Model_Theme', null, array(), '', false);

        /** @var $themeCustomized Mage_Core_Model_Theme */
        $themeCustomized = $this->getMock('Mage_Core_Model_Theme', array('getCustomizationPath'), array(), '', false);
        $themeCustomized->expects($this->any())
            ->method('getCustomizationPath')
            ->will($this->returnValue($customizationPath));

        /** @var $customizedPhysical Mage_Core_Model_Theme */
        $customizedPhysical = $this->getMock('Mage_Core_Model_Theme',
            array('getCustomizationPath', 'getThemePath'), array(), '', false);
        $customizedPhysical->expects($this->any())
            ->method('getCustomizationPath')
            ->will($this->returnValue($customizationPath));
        $customizedPhysical->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($themePath));

        /** @var $themeInherited Mage_Core_Model_Theme */
        $themeInherited = $this->getMock('Mage_Core_Model_Theme', array('getParentTheme'), array(), '', false);
        $themeInherited->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentTheme));

        /** @var $themeComplicated Mage_Core_Model_Theme */
        $themeComplicated = $this->getMock('Mage_Core_Model_Theme',
            array('getCustomizationPath', 'getThemePath', 'getParentTheme'), array(), '', false);
        $themeComplicated->expects($this->any())
            ->method('getCustomizationPath')
            ->will($this->returnValue($customizationPath));
        $themeComplicated->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($themePath));
        $themeComplicated->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentTheme));

        return array(
            array($themeSimple, $file, 'module_view_dir/area51/test.txt', 'module_view_dir/area51/test.txt'),
            array($themeSimple, $file, null, 'module_view_dir/area51/test.txt'),
            array($themeCustomized, $file, 'custom/test.txt', 'custom/test.txt'),
            array($themeCustomized, $file, 'module_view_dir/area51/test.txt', 'module_view_dir/area51/test.txt'),
            array($themeCustomized, $file, null, 'module_view_dir/area51/test.txt'),
            array($customizedPhysical, $file, 'custom/test.txt', 'custom/test.txt'),
            array($customizedPhysical, $file, 'design_dir/area51/theme_path/test.txt',
                'design_dir/area51/theme_path/test.txt'
            ),
            array($customizedPhysical, $file, 'design_dir/area51/theme_path/Mage_Core11/test.txt',
                'design_dir/area51/theme_path/Mage_Core11/test.txt'
            ),
            array($customizedPhysical, $file, 'module_view_dir/area51/test.txt',
                'module_view_dir/area51/test.txt'
            ),
            array($customizedPhysical, $file, null, 'module_view_dir/area51/test.txt'),
            array($themeInherited, $file, 'design_dir/area51/parent_theme_path/test.txt',
                'design_dir/area51/parent_theme_path/test.txt'
            ),
            array($themeInherited, $file, 'design_dir/area51/parent_theme_path/Mage_Core11/test.txt',
                'design_dir/area51/parent_theme_path/Mage_Core11/test.txt'
            ),
            array($themeInherited, $file, 'module_view_dir/area51/test.txt', 'module_view_dir/area51/test.txt'),
            array($themeInherited, $file, null, 'module_view_dir/area51/test.txt'),
            array($themeComplicated, $file, 'custom/test.txt', 'custom/test.txt'),
            array($themeComplicated, $file, 'design_dir/area51/theme_path/test.txt',
                'design_dir/area51/theme_path/test.txt'
            ),
            array($themeComplicated, $file, 'design_dir/area51/parent_theme_path/test.txt',
                'design_dir/area51/parent_theme_path/test.txt'
            ),
            array($themeComplicated, $file, 'design_dir/area51/theme_path/Mage_Core11/test.txt',
                'design_dir/area51/theme_path/Mage_Core11/test.txt'
            ),
            array($themeComplicated, $file, 'design_dir/area51/parent_theme_path/Mage_Core11/test.txt',
                'design_dir/area51/parent_theme_path/Mage_Core11/test.txt'
            ),
            array($themeComplicated, $file, 'module_view_dir/area51/test.txt', 'module_view_dir/area51/test.txt'),
            array($themeComplicated, $file, null, 'module_view_dir/area51/test.txt'),
        );
    }

    /**
     * @dataProvider getLocaleFileDataProvider
     * @param Mage_Core_Model_Theme|PHPUnit_Framework_MockObject_MockObject $theme
     * @param string $file
     * @param string $targetFile
     * @param string $expectedFileName
     * @cover Mage_Core_Model_Design_Fallback::_fallback()
     */
    public function testGetLocaleFile($theme, $file, $targetFile, $expectedFileName)
    {
        $designDir = 'design_dir';

        $filesystem = $this->_getFileSystemMock($targetFile);
        $objectManager = $this->_getObjectManagerMock();
        $dirs = $this->_getDirsMock();

        $dirs->expects($this->any())
            ->method('getDir')
            ->with(Mage_Core_Model_Dir::THEMES)
            ->will($this->returnValue($designDir));

        $data = array(
            'area'       => 'area51',
            'locale'     => 'en_EN',
            'themeModel' => $theme,
        );

        $fallback = new Mage_Core_Model_Design_Fallback($dirs, $objectManager, $filesystem, $data);
        $filename = $fallback->getLocaleFile($file);

        $this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $expectedFileName), $filename);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getLocaleFileDataProvider()
    {
        $customizationPath = 'custom';
        $themePath = 'theme_path';
        $parentThemePath = 'parent_theme_path';
        $grandParentPath = 'grand_parent_theme_path';
        $file = 'test.txt';

        // 0. Parent and grand parent themes
        /** @var $parentTheme Mage_Core_Model_Theme */
        $parentTheme = $this->getMock('Mage_Core_Model_Theme', array('getThemePath'), array(), '', false);
        $parentTheme->expects($this->any())->method('getThemePath')->will($this->returnValue($parentThemePath));

        /** @var $grandParentTheme Mage_Core_Model_Theme */
        $grandParentTheme = $this->getMock('Mage_Core_Model_Theme', array('getThemePath'), array(), '', false);
        $grandParentTheme->expects($this->any())->method('getThemePath')->will($this->returnValue($grandParentPath));

        /** @var $parentThemeInherited Mage_Core_Model_Theme */
        $parentThemeInherited = $this->getMock('Mage_Core_Model_Theme',
            array('getThemePath', 'getParentTheme'), array(), '', false);
        $parentThemeInherited->expects($this->any())->method('getThemePath')
            ->will($this->returnValue($parentThemePath));
        $parentThemeInherited->expects($this->any())->method('getParentTheme')
            ->will($this->returnValue($grandParentTheme));

        // 1.
        /** @var $themeSimple Mage_Core_Model_Theme */
        $themeSimple = $this->getMock('Mage_Core_Model_Theme', null, array(), '', false);

        // 2.
        /** @var $themeCustomized Mage_Core_Model_Theme */
        $themeCustomized = $this->getMock('Mage_Core_Model_Theme', array('getCustomizationPath'), array(), '', false);
        $themeCustomized->expects($this->any())->method('getCustomizationPath')
            ->will($this->returnValue($customizationPath));

        // 3.
        /** @var $customizedPhysical Mage_Core_Model_Theme */
        $customizedPhysical = $this->getMock('Mage_Core_Model_Theme',
            array('getCustomizationPath', 'getThemePath'), array(), '', false);
        $customizedPhysical->expects($this->any())
            ->method('getCustomizationPath')
            ->will($this->returnValue($customizationPath));
        $customizedPhysical->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($themePath));

        // 4.
        /** @var $themeInherited Mage_Core_Model_Theme */
        $themeInherited = $this->getMock('Mage_Core_Model_Theme', array('getParentTheme'), array(), '', false);
        $themeInherited->expects($this->any())->method('getParentTheme')->will($this->returnValue($parentTheme));

        // 5.
        /** @var $themeComplicated Mage_Core_Model_Theme */
        $themeComplicated = $this->getMock('Mage_Core_Model_Theme',
            array('getCustomizationPath', 'getThemePath', 'getParentTheme'), array(), '', false);
        $themeComplicated->expects($this->any())
            ->method('getCustomizationPath')
            ->will($this->returnValue($customizationPath));
        $themeComplicated->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($themePath));
        $themeComplicated->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentTheme));

        // 6.
        /** @var $themeInheritedTwice Mage_Core_Model_Theme */
        $themeInheritedTwice = $this->getMock('Mage_Core_Model_Theme', array('getParentTheme'), array(), '', false);
        $themeInheritedTwice->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentThemeInherited));

        return array(
            array($themeSimple, $file, null, ''),
            array($themeCustomized, $file, 'custom/test.txt', 'custom/test.txt'),
            array($themeCustomized, $file, null, 'custom/test.txt'),
            array($customizedPhysical, $file, 'custom/test.txt', 'custom/test.txt'),
            array($customizedPhysical, $file, 'design_dir/area51/theme_path/locale/en_EN/test.txt',
                'design_dir/area51/theme_path/locale/en_EN/test.txt'
            ),
            array($customizedPhysical, $file, null, 'design_dir/area51/theme_path/locale/en_EN/test.txt'),
            array($themeInherited, $file, 'design_dir/area51/parent_theme_path/locale/en_EN/test.txt',
                'design_dir/area51/parent_theme_path/locale/en_EN/test.txt'
            ),
            array($themeInherited, $file, null, 'design_dir/area51/parent_theme_path/locale/en_EN/test.txt'),
            array($themeComplicated, $file, 'custom/test.txt', 'custom/test.txt'),
            array($themeComplicated, $file, 'design_dir/area51/theme_path/locale/en_EN/test.txt',
                'design_dir/area51/theme_path/locale/en_EN/test.txt'
            ),
            array($themeComplicated, $file, 'design_dir/area51/parent_theme_path/locale/en_EN/test.txt',
                'design_dir/area51/parent_theme_path/locale/en_EN/test.txt'
            ),
            array($themeComplicated, $file, null, 'design_dir/area51/parent_theme_path/locale/en_EN/test.txt'),
            array($themeInheritedTwice, $file, 'design_dir/area51/parent_theme_path/locale/en_EN/test.txt',
                'design_dir/area51/parent_theme_path/locale/en_EN/test.txt'
            ),
            array($themeInheritedTwice, $file, 'design_dir/area51/grand_parent_theme_path/locale/en_EN/test.txt',
                'design_dir/area51/grand_parent_theme_path/locale/en_EN/test.txt'
            ),
            array($themeInheritedTwice, $file, null, 'design_dir/area51/grand_parent_theme_path/locale/en_EN/test.txt'),
        );
    }

    /**
     * @dataProvider getViewFileDataProvider
     * @param Mage_Core_Model_Theme|PHPUnit_Framework_MockObject_MockObject $theme
     * @param string $file
     * @param string $targetFile
     * @param string $expectedFileName
     * @cover Mage_Core_Model_Design_Fallback::_fallback()
     */
    public function testGetViewFile($theme, $file, $targetFile, $expectedFileName)
    {
        $designDir = 'design_dir';
        $moduleDir = 'module_view_dir';
        $jsDir = 'js_dir';
        $module = 'Mage_Core11';

        $filesystem = $this->_getFileSystemMock($targetFile);
        $objectManager = $this->_getObjectManagerMock();
        $dirs = $this->_getDirsMock();

        $configModel = $this->getMock('Mage_Core_Model_Config', array('getModuleDir'), array(), '', false);

        $configModel->expects($this->any())
            ->method('getModuleDir')
            ->with($this->equalTo('view'), $this->equalTo($module))
            ->will($this->returnValue($moduleDir));

        $objectManager->expects($this->any())
            ->method('get')
            ->with('Mage_Core_Model_Config')
            ->will($this->returnValue($configModel));

        $dirs->expects($this->at(0))
            ->method('getDir')
            ->with(Mage_Core_Model_Dir::THEMES)
            ->will($this->returnValue($designDir));

        $dirs->expects($this->at(1))
            ->method('getDir')
            ->with(Mage_Core_Model_Dir::PUB_LIB)
            ->will($this->returnValue($jsDir));

        $data = array(
            'area'       => 'area51',
            'locale'     => 'en_EN',
            'themeModel' => $theme,
        );

        $fallback = new Mage_Core_Model_Design_Fallback($dirs, $objectManager, $filesystem, $data);
        $filename = $fallback->getViewFile($file, $module);

        $this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $expectedFileName), $filename);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getViewFileDataProvider()
    {
        $customizationPath = 'custom';
        $themePath = 'theme_path';
        $parentThemePath = 'parent_theme_path';
        $grandParentThemePath = 'grand_parent_theme_path';
        $file = 'test.txt';

        // 0. Parent and grand parent themes
        /** @var $parentTheme Mage_Core_Model_Theme */
        $parentTheme = $this->getMock('Mage_Core_Model_Theme', array('getThemePath'), array(), '', false);
        $parentTheme->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($parentThemePath));

        /** @var $grandParentTheme Mage_Core_Model_Theme */
        $grandParentTheme = $this->getMock('Mage_Core_Model_Theme', array('getThemePath'), array(), '', false);
        $grandParentTheme->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($grandParentThemePath));

        /** @var $parentThemeInherited Mage_Core_Model_Theme */
        $parentThemeInherited = $this->getMock('Mage_Core_Model_Theme',
            array('getThemePath', 'getParentTheme'), array(), '', false);
        $parentThemeInherited->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($parentThemePath));
        $parentThemeInherited->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($grandParentTheme));

        // 1.
        /** @var $themeSimple Mage_Core_Model_Theme */
        $themeSimple = $this->getMock('Mage_Core_Model_Theme', null, array(), '', false);

        // 2.
        /** @var $themeCustomized Mage_Core_Model_Theme */
        $themeCustomized = $this->getMock('Mage_Core_Model_Theme', array('getCustomizationPath'), array(), '', false);
        $themeCustomized->expects($this->any())
            ->method('getCustomizationPath')
            ->will($this->returnValue($customizationPath));

        // 3.
        /** @var $customizedPhysical Mage_Core_Model_Theme */
        $customizedPhysical = $this->getMock('Mage_Core_Model_Theme',
            array('getCustomizationPath', 'getThemePath'), array(), '', false);
        $customizedPhysical->expects($this->any())
            ->method('getCustomizationPath')
            ->will($this->returnValue($customizationPath));
        $customizedPhysical->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($themePath));

        // 4.
        /** @var $themeInherited Mage_Core_Model_Theme */
        $themeInherited = $this->getMock('Mage_Core_Model_Theme', array('getParentTheme'), array(), '', false);
        $themeInherited->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentTheme));

        // 5.
        /** @var $themeComplicated Mage_Core_Model_Theme */
        $themeComplicated = $this->getMock('Mage_Core_Model_Theme',
            array('getCustomizationPath', 'getThemePath', 'getParentTheme'), array(), '', false);
        $themeComplicated->expects($this->any())
            ->method('getCustomizationPath')
            ->will($this->returnValue($customizationPath));
        $themeComplicated->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($themePath));
        $themeComplicated->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentTheme));

        // 6.
        /** @var $themeInheritedTwice Mage_Core_Model_Theme */
        $themeInheritedTwice = $this->getMock('Mage_Core_Model_Theme', array('getParentTheme'), array(), '', false);
        $themeInheritedTwice->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentThemeInherited));

        return array(
            array($themeSimple, $file, 'module_view_dir/area51/locale/en_EN/test.txt',
                'module_view_dir/area51/locale/en_EN/test.txt'
            ),
            array($themeSimple, $file, 'module_view_dir/area51/test.txt', 'module_view_dir/area51/test.txt'),
            array($themeSimple, $file, 'js_dir/test.txt', 'js_dir/test.txt'),
            array($themeSimple, $file, null, 'js_dir/test.txt'),
            array($themeCustomized, $file, 'custom/test.txt', 'custom/test.txt'),
            array($themeCustomized, $file, 'module_view_dir/area51/locale/en_EN/test.txt',
                'module_view_dir/area51/locale/en_EN/test.txt'
            ),
            array($themeCustomized, $file, 'module_view_dir/area51/test.txt', 'module_view_dir/area51/test.txt'),
            array($themeCustomized, $file, 'js_dir/test.txt', 'js_dir/test.txt'),
            array($themeCustomized, $file, null, 'js_dir/test.txt'),
            array($customizedPhysical, $file, 'custom/test.txt', 'custom/test.txt'),
            array($customizedPhysical, $file, 'design_dir/area51/theme_path/locale/en_EN/test.txt',
                'design_dir/area51/theme_path/locale/en_EN/test.txt'
            ),
            array($customizedPhysical, $file, 'design_dir/area51/theme_path/test.txt',
                'design_dir/area51/theme_path/test.txt'
            ),
            array($customizedPhysical, $file, 'design_dir/area51/theme_path/locale/en_EN/Mage_Core11/test.txt',
                'design_dir/area51/theme_path/locale/en_EN/Mage_Core11/test.txt'
            ),
            array($customizedPhysical, $file, 'design_dir/area51/theme_path/Mage_Core11/test.txt',
                'design_dir/area51/theme_path/Mage_Core11/test.txt'
            ),
            array($customizedPhysical, $file, 'module_view_dir/area51/locale/en_EN/test.txt',
                'module_view_dir/area51/locale/en_EN/test.txt'
            ),
            array($customizedPhysical, $file, 'module_view_dir/area51/test.txt',
                'module_view_dir/area51/test.txt'
            ),
            array($customizedPhysical, $file, 'js_dir/test.txt', 'js_dir/test.txt'),
            array($customizedPhysical, $file, null, 'js_dir/test.txt'),
            array($themeInherited, $file, 'design_dir/area51/parent_theme_path/locale/en_EN/test.txt',
                'design_dir/area51/parent_theme_path/locale/en_EN/test.txt'
            ),
            array($themeInherited, $file, 'design_dir/area51/parent_theme_path/test.txt',
                'design_dir/area51/parent_theme_path/test.txt'
            ),
            array($themeInherited, $file, 'design_dir/area51/parent_theme_path/locale/en_EN/Mage_Core11/test.txt',
                'design_dir/area51/parent_theme_path/locale/en_EN/Mage_Core11/test.txt'
            ),
            array($themeInherited, $file, 'design_dir/area51/parent_theme_path/Mage_Core11/test.txt',
                'design_dir/area51/parent_theme_path/Mage_Core11/test.txt'
            ),
            array($themeInherited, $file, 'module_view_dir/area51/locale/en_EN/test.txt',
                'module_view_dir/area51/locale/en_EN/test.txt'
            ),
            array($themeInherited, $file, 'module_view_dir/area51/test.txt', 'module_view_dir/area51/test.txt'),
            array($themeInherited, $file, 'js_dir/test.txt', 'js_dir/test.txt'),
            array($themeInherited, $file, null, 'js_dir/test.txt'),
            array($themeComplicated, $file, 'custom/test.txt', 'custom/test.txt'),
            array($themeComplicated, $file, 'design_dir/area51/theme_path/locale/en_EN/test.txt',
                'design_dir/area51/theme_path/locale/en_EN/test.txt'
            ),
            array($themeComplicated, $file, 'design_dir/area51/theme_path/test.txt',
                'design_dir/area51/theme_path/test.txt'),
            array($themeComplicated, $file, 'design_dir/area51/parent_theme_path/locale/en_EN/test.txt',
                'design_dir/area51/parent_theme_path/locale/en_EN/test.txt'
            ),
            array($themeComplicated, $file, 'design_dir/area51/parent_theme_path/test.txt',
                'design_dir/area51/parent_theme_path/test.txt'
            ),
            array($themeComplicated, $file, 'design_dir/area51/theme_path/locale/en_EN/Mage_Core11/test.txt',
                'design_dir/area51/theme_path/locale/en_EN/Mage_Core11/test.txt'
            ),
            array($themeComplicated, $file, 'design_dir/area51/theme_path/Mage_Core11/test.txt',
                'design_dir/area51/theme_path/Mage_Core11/test.txt'
            ),
            array($themeComplicated, $file, 'design_dir/area51/parent_theme_path/locale/en_EN/Mage_Core11/test.txt',
                'design_dir/area51/parent_theme_path/locale/en_EN/Mage_Core11/test.txt'
            ),
            array($themeComplicated, $file, 'design_dir/area51/parent_theme_path/Mage_Core11/test.txt',
                'design_dir/area51/parent_theme_path/Mage_Core11/test.txt'
            ),
            array($themeComplicated, $file, 'module_view_dir/area51/locale/en_EN/test.txt',
                'module_view_dir/area51/locale/en_EN/test.txt'
            ),
            array($themeComplicated, $file, 'module_view_dir/area51/test.txt', 'module_view_dir/area51/test.txt'),
            array($themeComplicated, $file, 'js_dir/test.txt', 'js_dir/test.txt'),
            array($themeComplicated, $file, null, 'js_dir/test.txt'),
            array($themeInheritedTwice, $file, 'design_dir/area51/parent_theme_path/locale/en_EN/test.txt',
                'design_dir/area51/parent_theme_path/locale/en_EN/test.txt'
            ),
            array($themeInheritedTwice, $file, 'design_dir/area51/parent_theme_path/test.txt',
                'design_dir/area51/parent_theme_path/test.txt'
            ),
            array($themeInheritedTwice, $file, 'design_dir/area51/grand_parent_theme_path/locale/en_EN/test.txt',
                'design_dir/area51/grand_parent_theme_path/locale/en_EN/test.txt'
            ),
            array($themeInheritedTwice, $file, 'design_dir/area51/grand_parent_theme_path/test.txt',
                'design_dir/area51/grand_parent_theme_path/test.txt'
            ),
            array($themeInheritedTwice, $file, 'design_dir/area51/parent_theme_path/locale/en_EN/Mage_Core11/test.txt',
                'design_dir/area51/parent_theme_path/locale/en_EN/Mage_Core11/test.txt'
            ),
            array($themeInheritedTwice, $file, 'design_dir/area51/parent_theme_path/Mage_Core11/test.txt',
                'design_dir/area51/parent_theme_path/Mage_Core11/test.txt'
            ),
            array($themeInheritedTwice, $file,
                'design_dir/area51/grand_parent_theme_path/locale/en_EN/Mage_Core11/test.txt',
                'design_dir/area51/grand_parent_theme_path/locale/en_EN/Mage_Core11/test.txt'
            ),
            array($themeInheritedTwice, $file, 'design_dir/area51/grand_parent_theme_path/Mage_Core11/test.txt',
                'design_dir/area51/grand_parent_theme_path/Mage_Core11/test.txt'
            ),
            array($themeInheritedTwice, $file, 'module_view_dir/area51/locale/en_EN/test.txt',
                'module_view_dir/area51/locale/en_EN/test.txt'
            ),
            array($themeInheritedTwice, $file, 'module_view_dir/area51/test.txt', 'module_view_dir/area51/test.txt'),
            array($themeInheritedTwice, $file, 'js_dir/test.txt', 'js_dir/test.txt'),
            array($themeInheritedTwice, $file, null, 'js_dir/test.txt'),
        );
    }

    /**
     * @param array $data
     * @return Mage_Core_Model_Config_Options|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOptionsMock(array $data)
    {
        /** @var $options Mage_Core_Model_Config_Options */
        $options = $this->getMock('Mage_Core_Model_Config_Options',
            array('getDesignDir', 'getJsDir'), array(), '', false);
        if (isset($data['designDir'])) {
            $options->expects($this->any())
                ->method('getDesignDir')
                ->will($this->returnValue($data['designDir']));
        }
        if (isset($data['jsDir'])) {
            $options->expects($this->any())
                ->method('getJsDir')
                ->will($this->returnValue($data['jsDir']));
        }

        return $options;
    }

    /**
     * @param array $data
     * @param array $methods
     * @return Mage_Core_Model_Config|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getAppConfigMock(array $data, $methods = array('getOptions'))
    {
        $options = $this->_getOptionsMock($data);

        /** @var $appConfig Mage_Core_Model_Config */
        $appConfig = $this->getMock('Mage_Core_Model_Config', $methods, array(), '', false);
        $appConfig->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        return $appConfig;
    }

    /**
     * @param string $targetFile
     * @return Magento_Filesystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getFileSystemMock($targetFile)
    {
        $targetFile = str_replace('/', DIRECTORY_SEPARATOR, $targetFile);
            /** @var $filesystem Magento_Filesystem */
        $filesystem = $this->getMock('Magento_Filesystem', array('has'), array(), '', false);
        $filesystem->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(
                function ($tryFile) use ($targetFile) {
                    return ($tryFile == $targetFile);
                }
        ));

        return $filesystem;
    }

    /**
     * @return Magento_ObjectManager_Zend
     */
    protected function _getObjectManagerMock()
    {
        /** @var $objectManager Magento_ObjectManager_Zend */
        $objectManager = $this->getMock('Magento_ObjectManager_Zend', array('get'), array(), '', false);
        return $objectManager;
    }

    /**
     * @return Mage_Core_Model_Dir
     */
    protected function _getDirsMock()
    {
        /** @var $dirs Mage_Core_Model_Dir */
        $dirs = $this->getMock('Mage_Core_Model_Dir', array('getDir'), array(), '', false);
        return $dirs;
    }
}
