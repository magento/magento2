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

namespace Magento\Framework\View\File\FileList;

class CollatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collator
     */
    protected $_model;

    /**
     * @var \Magento\Framework\View\File[]
     */
    protected $_originFiles;

    /**
     * @var \Magento\Framework\View\File
     */
    protected $_baseFile;

    /**
     * @var \Magento\Framework\View\File
     */
    protected $_themeFile;

    protected function setUp()
    {
        $this->_baseFile = $this->_createViewFile('fixture.xml', 'Fixture_TestModule');
        $this->_themeFile = $this->_createViewFile('fixture.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->_originFiles = array(
            $this->_baseFile->getFileIdentifier() => $this->_baseFile,
            $this->_themeFile->getFileIdentifier() => $this->_themeFile
        );
        $this->_model = new Collator();
    }

    /**
     * Return newly created theme view file with a mocked theme
     *
     * @param string $filename
     * @param string $module
     * @param string|null $themeFullPath
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\File
     */
    protected function _createViewFile($filename, $module, $themeFullPath = null)
    {
        $theme = null;
        if ($themeFullPath !== null) {
            $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
            $theme->expects($this->any())->method('getFullPath')->will($this->returnValue($themeFullPath));
        }
        return new \Magento\Framework\View\File($filename, $module, $theme);
    }

    public function testCollateBaseFile()
    {
        $file = $this->_createViewFile('test/fixture.xml', 'Fixture_TestModule');
        $this->assertSame(
            array($file->getFileIdentifier() => $file, $this->_themeFile->getFileIdentifier() => $this->_themeFile),
            $this->_model->collate(array($file), $this->_originFiles)
        );
    }

    public function testReplaceThemeFile()
    {
        $file = $this->_createViewFile('test/fixture.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->assertSame(
            array($this->_baseFile->getFileIdentifier() => $this->_baseFile, $file->getFileIdentifier() => $file),
            $this->_model->collate(array($file), $this->_originFiles)
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Overriding view file 'new.xml' does not match to any of the files
     */
    public function testReplaceBaseFileException()
    {
        $file = $this->_createViewFile('new.xml', 'Fixture_TestModule');
        $this->_model->collate(array($file), $this->_originFiles);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Overriding view file 'test/fixture.xml' does not match to any of the files
     */
    public function testReplaceBaseFileEmptyThemePathException()
    {
        $file = $this->_createViewFile('test/fixture.xml', 'Fixture_TestModule', '');
        $this->_model->collate(array($file), $this->_originFiles);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Overriding view file 'new.xml' does not match to any of the files
     */
    public function testReplaceThemeFileException()
    {
        $file = $this->_createViewFile('new.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->_model->collate(array($file), $this->_originFiles);
    }
}
