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

/**
 * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
 */
class Mage_Core_Model_Page_Asset_MergedTest extends PHPUnit_Framework_TestCase
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

    public static function setUpBeforeClass()
    {
        /** @var $service Mage_Core_Model_View_Service */
        $service = Mage::getObjectManager()->get('Mage_Core_Model_View_Service');
        self::$_themePublicDir = $service->getPublicDir();

        /** @var Mage_Core_Model_Dir $dirs */
        $dirs = Mage::getObjectManager()->get('Mage_Core_Model_Dir');
        self::$_viewPublicMergedDir = $dirs->getDir(Mage_Core_Model_Dir::PUB_VIEW_CACHE)
            . DIRECTORY_SEPARATOR . Mage_Core_Model_Page_Asset_Merged::PUBLIC_MERGE_DIR;
    }

    public function setUp()
    {
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
            Mage::PARAM_APP_DIRS => array(
                Mage_Core_Model_Dir::THEMES => realpath(__DIR__ . '/../../_files/design')
            )
        ));
        Mage::getDesign()->setDesignTheme('package/default');
    }

    public function tearDown()
    {
        $filesystem = Mage::getObjectManager()->create('Magento_Filesystem');
        $filesystem->delete(self::$_themePublicDir . '/frontend');
        $filesystem->delete(self::$_viewPublicMergedDir);
    }

    /**
     * Build model, containing the provided assets
     *
     * @param array $files
     * @param string $contentType
     * @return Mage_Core_Model_Page_Asset_Merged
     */
    protected function _buildModel(array $files, $contentType)
    {
        $assets = array();
        foreach ($files as $file) {
            $assets[] = Mage::getModel('Mage_Core_Model_Page_Asset_ViewFile',
                array('file' => $file, 'contentType' => $contentType));
        }
        $model = Mage::getModel('Mage_Core_Model_Page_Asset_Merged', array('assets' => $assets));
        return $model;
    }

    /**
     * @param string $contentType
     * @param array $files
     * @param string $expectedFilename
     * @param array $related
     * @dataProvider getUrlDataProvider
     * @magentoConfigFixture current_store dev/css/merge_css_files 1
     * @magentoConfigFixture current_store dev/js/merge_files 1
     * @magentoConfigFixture current_store dev/static/sign 0
     */
    public function testMerging($contentType, $files, $expectedFilename, $related = array())
    {
        $resultingFile = self::$_viewPublicMergedDir . '/' . $expectedFilename;
        $this->assertFileNotExists($resultingFile);

        $model = $this->_buildModel($files, $contentType);

        $this->assertCount(1, $model);

        $model->rewind();
        $asset = $model->current();
        $mergedUrl = $asset->getUrl();
        $this->assertEquals($expectedFilename, basename($mergedUrl));

        $this->assertFileExists($resultingFile);
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
     * @dataProvider getUrlDataProvider
     * @magentoConfigFixture current_store dev/css/merge_css_files 1
     * @magentoConfigFixture current_store dev/js/merge_files 1
     * @magentoConfigFixture current_store dev/static/sign 1
     */
    public function testMergingAndSigning($contentType, $files, $expectedFilename, $related = array())
    {
        $model = $this->_buildModel($files, $contentType);

        $asset = $model->current();
        $mergedUrl = $asset->getUrl();
        $mergedFileName = basename($mergedUrl);
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
    public static function getUrlDataProvider()
    {
        return array(
            array(
                Mage_Core_Model_View_Publisher::CONTENT_TYPE_CSS,
                array(
                    'mage/calendar.css',
                    'css/file.css',
                ),
                'c028046b30a5cc2eee49498db083828d.css',
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
                Mage_Core_Model_View_Publisher::CONTENT_TYPE_JS,
                array(
                    'mage/calendar.js',
                    'scripts.js',
                ),
                'd4417a959615e25a2f5cebfc74269374.js',
            ),
        );
    }
}
