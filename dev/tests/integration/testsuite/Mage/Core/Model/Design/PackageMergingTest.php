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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
 */
class Mage_Core_Model_Design_PackageMergingTest extends PHPUnit_Framework_TestCase
{
    /**
     * Path to the public directory for view files
     *
     * @var string
     */
    protected static $_themePublicDir;

    /**
     * Path to the public directory for merged view files
     *
     * @var string
     */
    protected static $_viewPublicMergedDir;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_model = null;

    public static function setUpBeforeClass()
    {
        self::$_themePublicDir = Mage::getDesign()->getPublicDir();
        self::$_viewPublicMergedDir = self::$_themePublicDir . '/_merged';
    }

    protected function setUp()
    {
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
            Mage::PARAM_APP_DIRS => array(
                Mage_Core_Model_Dir::THEMES => dirname(__DIR__) . '/_files/design'
            )
        ));
        $this->_model = Mage::getSingleton('Mage_Core_Model_Design_Package');
        $this->_model->setDesignTheme('package/default');
    }

    protected function tearDown()
    {
        $filesystem = Mage::getObjectManager()->create('Magento_Filesystem');
        $filesystem->delete(self::$_themePublicDir . '/frontend');
        $filesystem->delete(self::$_viewPublicMergedDir);
    }

    /**
     * @magentoConfigFixture current_store dev/css/merge_css_files 1
     * @expectedException Magento_Exception
     */
    public function testMergeFilesException()
    {
        $this->_model->mergeFiles(array(
            'css/exception.css',
            'css/file.css',
        ), Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS);
        $this->assertFileNotExists(self::$_themePublicDir . '/frontend/package/default/en_US/access_violation.php');
    }

    /**
     * @param string $contentType
     * @param array $files
     * @param string $expectedFilename
     * @param array $related
     * @dataProvider mergeFilesDataProvider
     * @magentoConfigFixture current_store dev/css/merge_css_files 1
     * @magentoConfigFixture current_store dev/js/merge_files 1
     * @magentoConfigFixture current_store dev/static/sign 0
     */
    public function testMergeFiles($contentType, $files, $expectedFilename, $related = array())
    {
        if ($contentType == Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS) {
            $result = $this->_model->mergeFiles($files, Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS);
        } else {
            $result = $this->_model->mergeFiles($files, Mage_Core_Model_Design_Package::CONTENT_TYPE_JS);
        }
        $this->assertEquals($expectedFilename, basename($result));
        foreach ($related as $file) {
            $this->assertFileExists(
                self::$_themePublicDir . '/frontend/package/default/en_US/' . $file
            );
        }
    }

    /**
     * @param string $contentType
     * @param array $files
     * @param string $expectedFilename
     * @param array $related
     * @dataProvider mergeFilesDataProvider
     * @magentoConfigFixture current_store dev/css/merge_css_files 1
     * @magentoConfigFixture current_store dev/js/merge_files 1
     * @magentoConfigFixture current_store dev/static/sign 1
     */
    public function testMergeFilesSigned($contentType, $files, $expectedFilename, $related = array())
    {
        if ($contentType == Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS) {
            $result = $this->_model->mergeFiles($files, Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS);
        } else {
            $result = $this->_model->mergeFiles($files, Mage_Core_Model_Design_Package::CONTENT_TYPE_JS);
        }
        $mergedFileName = basename($result);
        $mergedFileName = preg_replace('/\?.*$/i', '', $mergedFileName);
        $this->assertEquals($expectedFilename, $mergedFileName);
        foreach ($related as $file) {
            $this->assertFileExists(
                self::$_themePublicDir . '/frontend/package/default/en_US/' . $file
            );
        }
    }

    /**
     * @return array
     */
    public function mergeFilesDataProvider()
    {
        return array(
            array(
                Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS,
                array(
                    'mage/calendar.css',
                    'css/file.css',
                ),
                '62f590d4535f5dca8e3d7923161eb5f4.css',
                array(
                    'css/file.css',
                    'recursive.css',
                    'recursive.gif',
                    'css/deep/recursive.css',
                    'recursive2.gif',
                    'css/body.gif',
                    'css/1.gif',
                    'h1.gif',
                    'images/h2.gif',
                    'Namespace_Module/absolute_valid_module.gif',
                    'Mage_Page/favicon.ico', // non-fixture file from real module
                ),
            ),
            array(
                Mage_Core_Model_Design_Package::CONTENT_TYPE_JS,
                array(
                    'mage/calendar.js',
                    'scripts.js',
                ),
                '8fa0d695232b6117977c2b29c34e5901.js',
            ),
        );
    }

    /**
     * @magentoConfigFixture current_store dev/js/merge_files 1
     */
    public function testMergeFilesModification()
    {
        $files = array(
            'mage/calendar.js',
            'scripts.js',
        );

        $resultingFile = self::$_viewPublicMergedDir . '/8fa0d695232b6117977c2b29c34e5901.js';
        $this->assertFileNotExists($resultingFile);

        // merge first time
        $this->_model->mergeFiles($files, Mage_Core_Model_Design_Package::CONTENT_TYPE_JS);
        $this->assertFileExists($resultingFile);

    }

    /**
     * @magentoConfigFixture current_store dev/js/merge_files 1
     */
    public function testCleanMergedJsCss()
    {
        $this->assertFileNotExists(self::$_viewPublicMergedDir);

        $this->_model->mergeFiles(array(
            'mage/calendar.js',
            'scripts.js',
        ), Mage_Core_Model_Design_Package::CONTENT_TYPE_JS);
        $this->assertFileExists(self::$_viewPublicMergedDir);
        $filesFound = false;
        foreach (new RecursiveDirectoryIterator(self::$_viewPublicMergedDir) as $fileInfo) {
            if ($fileInfo->isFile()) {
                $filesFound = true;
                break;
            }
        }
        $this->assertTrue($filesFound, 'No files found in the merged directory.');

        $this->_model->cleanMergedJsCss();
        $this->assertFileNotExists(self::$_viewPublicMergedDir);
    }
}
