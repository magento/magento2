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

namespace Magento\Framework\View\File\Collector\Override;

class ThemeModularTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\File\Collector\Override\ThemeModular
     */
    private $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_directory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_fileFactory;

    protected function setUp()
    {
        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', array('getDirectoryRead'), array(), '', false);
        $this->_directory = $this->getMock('Magento\Framework\Filesystem\Directory\Read', array(), array(), '', false);
        $this->_directory->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnArgument(0));

        $filesystem->expects($this->any())->method('getDirectoryRead')
            ->with($this->equalTo(\Magento\Framework\App\Filesystem::THEMES_DIR))
            ->will($this->returnValue($this->_directory));
        $this->_fileFactory = $this->getMock('Magento\Framework\View\File\Factory', array(), array(), '', false);
        $this->_model = new \Magento\Framework\View\File\Collector\Override\ThemeModular(
            $filesystem, $this->_fileFactory, 'override/theme'
        );
    }

    public function testGetFiles()
    {
        $grandparentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $grandparentTheme->expects($this->once())->method('getCode')->will($this->returnValue('grand_parent_theme'));

        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->once())->method('getCode')->will($this->returnValue('parent_theme'));
        $parentTheme->expects($this->once())->method('getParentTheme')->will($this->returnValue($grandparentTheme));

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue('area/theme_path'));
        $theme->expects($this->once())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $filePathOne = 'design/area/theme_path/Module_One/override/theme/parent_theme/1.xml';
        $filePathTwo = 'design/area/theme_path/Module_Two/override/theme/grand_parent_theme/2.xml';
        $this->_directory->expects($this->once())
            ->method('search')
            ->with($this->equalTo('area/theme_path/*_*/override/theme/*/*.xml'))
            ->will($this->returnValue(array($filePathOne, $filePathTwo)));

        $fileOne = new \Magento\Framework\View\File('1.xml', 'Module_One', $parentTheme);
        $fileTwo = new \Magento\Framework\View\File('2.xml', 'Module_Two', $grandparentTheme);
        $this->_fileFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap(array(
                array($filePathOne, 'Module_One', $parentTheme, false, $fileOne),
                array($filePathTwo, 'Module_Two', $grandparentTheme, false, $fileTwo),
            )))
        ;

        $this->assertSame(array($fileOne, $fileTwo), $this->_model->getFiles($theme, '*.xml'));
    }

    public function testGetFilesWithPreset()
    {
        $grandparentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $grandparentTheme->expects($this->once())->method('getCode')->will($this->returnValue('grand_parent_theme'));

        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->once())->method('getCode')->will($this->returnValue('parent_theme'));
        $parentTheme->expects($this->once())->method('getParentTheme')->will($this->returnValue($grandparentTheme));

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue('area/theme_path'));
        $theme->expects($this->once())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $filePathOne = 'design/area/theme_path/Module_Two/override/theme/grand_parent_theme/preset/3.xml';
        $this->_directory->expects($this->once())
            ->method('search')
            ->with('area/theme_path/*_*/override/theme/*/preset/3.xml')
            ->will($this->returnValue(array($filePathOne)))
        ;

        $fileOne = new \Magento\Framework\View\File('3.xml', 'Module_Two', $grandparentTheme);
        $this->_fileFactory
            ->expects($this->once())
            ->method('create')
            ->with($filePathOne, 'Module_Two', $grandparentTheme)
            ->will($this->returnValue($fileOne))
        ;

        $this->assertSame(array($fileOne), $this->_model->getFiles($theme, 'preset/3.xml'));
    }

    public function testGetFilesWrongAncestor()
    {
        $filePath = 'design/area/theme_path/Module_One/override/theme/parent_theme/1.xml';
        $this->setExpectedException(
            'Magento\Framework\Exception',
            "Trying to override modular view file '$filePath' for theme 'parent_theme'"
                . ", which is not ancestor of theme 'theme_path'"
        );

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue('area/theme_path'));
        $theme->expects($this->once())->method('getParentTheme')->will($this->returnValue(null));
        $theme->expects($this->once())->method('getCode')->will($this->returnValue('theme_path'));

        $this->_directory->expects($this->once())
            ->method('search')
            ->with('area/theme_path/*_*/override/theme/*/*.xml')
            ->will($this->returnValue(array($filePath)));

        $this->_model->getFiles($theme, '*.xml');
    }
}
