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
class Mage_Core_Model_Design_FileResolution_Strategy_FallbackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Design_Fallback_List_File
     */
    protected $_fallbackFile;

    /*
     * @var Mage_Core_Model_Design_Fallback_List_Locale
     */
    protected $_fallbackLocale;

    /**
     * @var Mage_Core_Model_Design_Fallback_List_View
     */
    protected $_fallbackViewFile;

    /**
     * @var Mage_Core_Model_Dir
     */
    protected $_dirs;

    /**
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    public function setUp()
    {
        $this->_objectManager = $this->getMock('Magento_ObjectManager');
        $this->_dirs = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false);
        $this->_fallbackFile =
            $this->getMock('Mage_Core_Model_Design_Fallback_List_File', array(), array($this->_dirs));
        $this->_fallbackLocale =
            $this->getMock('Mage_Core_Model_Design_Fallback_List_Locale', array(), array($this->_dirs));
        $this->_fallbackViewFile =
            $this->getMock('Mage_Core_Model_Design_Fallback_List_View', array(), array($this->_dirs));
        $this->_theme = $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false);
    }

    public function tearDown()
    {
        $this->_objectManager = null;
        $this->_dirs = null;
        $this->_fallbackFile = null;
        $this->_fallbackLocale = null;
        $this->_fallbackViewFile = null;
        $this->_theme = null;
    }

    /**
     * @dataProvider getFileDataProvider
     */
    public function testGetFile($fullModuleName, $namespace, $module, $targetFile, $expectedFileName)
    {
        $filesystem = $this->_getFileSystemMock($targetFile);

        $fallback = new Mage_Core_Model_Design_FileResolution_Strategy_Fallback($this->_objectManager, $filesystem,
            $this->_dirs, $this->_fallbackFile, $this->_fallbackLocale, $this->_fallbackViewFile);

        $params = array('area' => 'area', 'theme' => $this->_theme, 'namespace' => $namespace, 'module' => $module);

        $this->_fallbackFile->expects($this->once())
            ->method('getPatternDirs')
            ->with($params)
            ->will($this->returnValue(array('found_folder', 'not_found_folder')));

        $filename = $fallback->getFile('area', $this->_theme, 'file.txt', $fullModuleName);

        $this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $expectedFileName), $filename);
    }

    public function getFileDataProvider()
    {
        return array(
            'no module, file found' => array(
                null,
                null,
                null,
                'found_folder/file.txt',
                'found_folder/file.txt',
            ),
            'module, file found' => array(
                'Namespace_Module',
                'Namespace',
                'Module',
                'found_folder/file.txt',
                'found_folder/file.txt',
            ),
            'no module, file not found' => array(
                null,
                null,
                null,
                null,
                'not_found_folder/file.txt',
            ),
            'module, file not found' => array(
                'Namespace_Module',
                'Namespace',
                'Module',
                null,
                'not_found_folder/file.txt',
            ),
        );
    }

    /**
     * @dataProvider getLocaleFileDataProvider
     */
    public function testGetLocaleFile($targetFile, $expectedFileName)
    {
        $filesystem = $this->_getFileSystemMock($targetFile);

        $fallback = new Mage_Core_Model_Design_FileResolution_Strategy_Fallback($this->_objectManager, $filesystem,
            $this->_dirs, $this->_fallbackFile, $this->_fallbackLocale, $this->_fallbackViewFile);

        $params = array('area' => 'area', 'theme' => $this->_theme, 'locale' => 'locale');

        $this->_fallbackLocale->expects($this->once())
            ->method('getPatternDirs')
            ->with($params)
            ->will($this->returnValue(array('found_folder', 'not_found_folder')));

        $filename = $fallback->getLocaleFile('area', $this->_theme, 'locale', 'file.txt');

        $this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $expectedFileName), $filename);
    }

    public function getLocaleFileDataProvider()
    {
        return array(
            'file found' => array(
                'found_folder/file.txt',
                'found_folder/file.txt',
            ),
            'file not found' => array(
                null,
                'not_found_folder/file.txt',
            )
        );
    }

    /**
     * @dataProvider getFileDataProvider
     */
    public function testGetViewFile($fullModuleName, $namespace, $module, $targetFile, $expectedFileName)
    {
        $filesystem = $this->_getFileSystemMock($targetFile);

        $fallback = new Mage_Core_Model_Design_FileResolution_Strategy_Fallback($this->_objectManager, $filesystem,
            $this->_dirs, $this->_fallbackFile, $this->_fallbackLocale, $this->_fallbackViewFile);

        $params = array('area' => 'area', 'theme' => $this->_theme, 'namespace' => $namespace, 'module' => $module,
            'locale' => 'locale');

        $this->_fallbackViewFile->expects($this->once())
            ->method('getPatternDirs')
            ->with($params)
            ->will($this->returnValue(array('found_folder', 'not_found_folder')));

        $filename = $fallback->getViewFile('area', $this->_theme, 'locale', 'file.txt', $fullModuleName);

        $this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $expectedFileName), $filename);
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
}
