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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Layout\File;

class ListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Layout\File\ListFile
     */
    private $_model;

    /**
     * @var \Magento\Core\Model\Layout\File
     */
    private $_baseFile;

    /**
     * @var \Magento\Core\Model\Layout\File
     */
    private $_themeFile;

    protected function setUp()
    {
        $this->_baseFile = $this->_createLayoutFile('fixture.xml', 'Fixture_TestModule');
        $this->_themeFile = $this->_createLayoutFile('fixture.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->_model = new \Magento\Core\Model\Layout\File\ListFile();
        $this->_model->add(array($this->_baseFile, $this->_themeFile));
    }

    /**
     * Return newly created theme layout file with a mocked theme
     *
     * @param string $filename
     * @param string $module
     * @param string|null $themeFullPath
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\View\Design\ThemeInterface
     */
    protected function _createLayoutFile($filename, $module, $themeFullPath = null)
    {
        $theme = null;
        if ($themeFullPath !== null) {
            $theme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
            $theme->expects($this->any())->method('getFullPath')->will($this->returnValue($themeFullPath));
        }
        return new \Magento\Core\Model\Layout\File($filename, $module, $theme);
    }

    public function testGetAll()
    {
        $this->assertSame(array($this->_baseFile, $this->_themeFile), $this->_model->getAll());
    }

    public function testAddBaseFile()
    {
        $file = $this->_createLayoutFile('new.xml', 'Fixture_TestModule');
        $this->_model->add(array($file));
        $this->assertSame(array($this->_baseFile, $this->_themeFile, $file), $this->_model->getAll());
    }

    public function testAddThemeFile()
    {
        $file = $this->_createLayoutFile('new.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->_model->add(array($file));
        $this->assertSame(array($this->_baseFile, $this->_themeFile, $file), $this->_model->getAll());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Layout file 'test/fixture.xml' is indistinguishable from the file 'fixture.xml'
     */
    public function testAddBaseFileException()
    {
        $file = $this->_createLayoutFile('test/fixture.xml', 'Fixture_TestModule');
        $this->_model->add(array($file));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Layout file 'test/fixture.xml' is indistinguishable from the file 'fixture.xml'
     */
    public function testAddThemeFileException()
    {
        $file = $this->_createLayoutFile('test/fixture.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->_model->add(array($file));
    }

    public function testReplaceBaseFile()
    {
        $file = $this->_createLayoutFile('test/fixture.xml', 'Fixture_TestModule');
        $this->_model->replace(array($file));
        $this->assertSame(array($file, $this->_themeFile), $this->_model->getAll());
    }

    public function testReplaceThemeFile()
    {
        $file = $this->_createLayoutFile('test/fixture.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->_model->replace(array($file));
        $this->assertSame(array($this->_baseFile, $file), $this->_model->getAll());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Overriding layout file 'new.xml' does not match to any of the files
     */
    public function testReplaceBaseFileException()
    {
        $file = $this->_createLayoutFile('new.xml', 'Fixture_TestModule');
        $this->_model->replace(array($file));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Overriding layout file 'test/fixture.xml' does not match to any of the files
     */
    public function testReplaceBaseFileEmptyThemePathException()
    {
        $file = $this->_createLayoutFile('test/fixture.xml', 'Fixture_TestModule', '');
        $this->_model->replace(array($file));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Overriding layout file 'new.xml' does not match to any of the files
     */
    public function testReplaceThemeFileException()
    {
        $file = $this->_createLayoutFile('new.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->_model->replace(array($file));
    }
}
