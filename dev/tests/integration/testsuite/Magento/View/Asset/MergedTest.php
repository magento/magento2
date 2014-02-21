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

namespace Magento\View\Asset;

/**
 * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
 */
class MergedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Path to the public directory for view files
     *
     * @var \Magento\Filesystem\Directory\WriteInterface
     */
    protected static $_themePublicDir;

    /**
     * Path to the public directory for merged view files
     *
     * @var \Magento\Filesystem\Directory\WriteInterface
     */
    protected static $_viewPublicMergedDir;

    public static function setUpBeforeClass()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\App\Filesystem $filesystem */
        $filesystem = $objectManager->get('Magento\App\Filesystem');
        self::$_themePublicDir = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::STATIC_VIEW_DIR);
        self::$_viewPublicMergedDir = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::PUB_VIEW_CACHE_DIR);
    }

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(array(
            \Magento\App\Filesystem::PARAM_APP_DIRS => array(
                \Magento\App\Filesystem::THEMES_DIR => array('path' => dirname(dirname(__DIR__)) . '/_files/design'),
                \Magento\App\Filesystem::PUB_DIR => array('path' => BP),
            )
        ));
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State')->setAreaCode('frontend');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\DesignInterface')
            ->setDesignTheme('vendor_default');
    }

    protected function tearDown()
    {
        self::$_themePublicDir->delete('frontend');
        self::$_viewPublicMergedDir->delete(\Magento\View\Asset\Merged::PUBLIC_MERGE_DIR);
    }

    /**
     * Build model, containing the provided assets
     *
     * @param array $files
     * @param string $contentType
     * @return \Magento\View\Asset\Merged
     */
    protected function _buildModel(array $files, $contentType)
    {
        $assets = array();
        foreach ($files as $file) {
            $assets[] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\View\Asset\ViewFile',
                array('file' => $file, 'contentType' => $contentType));
        }
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\View\Asset\Merged', array('assets' => $assets));
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
        $this->markTestSkipped('Task: MAGETWO-18162');
        $resultingFile = self::$_viewPublicMergedDir->getAbsolutePath(
            \Magento\View\Asset\Merged::PUBLIC_MERGE_DIR . '/' . $expectedFilename
        );
        $this->assertFileNotExists($resultingFile);

        $model = $this->_buildModel($files, $contentType);

        $this->assertCount(1, $model);

        $model->rewind();
        $asset = $model->current();
        $mergedUrl = $asset->getUrl();
        $this->assertEquals($expectedFilename, basename($mergedUrl));

        $this->assertFileExists($resultingFile);
        foreach ($related as $file) {
            $this->assertFileExists(self::$_themePublicDir->getAbsolutePath('frontend/vendor_default/en_US/' . $file));
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
        $this->markTestSkipped('Task: MAGETWO-18162');
        $model = $this->_buildModel($files, $contentType);

        $asset = $model->current();
        $mergedUrl = $asset->getUrl();
        $mergedFileName = basename($mergedUrl);
        $mergedFileName = preg_replace('/\?.*$/i', '', $mergedFileName);
        $this->assertEquals($expectedFilename, $mergedFileName);

        foreach ($related as $file) {
            $this->assertFileExists(self::$_themePublicDir->getAbsolutePath('frontend/vendor_default/en_US/' . $file));
        }
    }

    /**
     * @return array
     */
    public static function getUrlDataProvider()
    {
        return array(
            array(
                \Magento\View\Publisher::CONTENT_TYPE_CSS,
                array(
                    'mage/calendar.css',
                    'css/file.css',
                ),
                'e6ae894165d22b7d57a0f5644b6ef160.css',
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
                    'Magento_Theme/favicon.ico', // non-fixture file from real module
                ),
            ),
            array(
                \Magento\View\Publisher::CONTENT_TYPE_JS,
                array(
                    'mage/calendar.js',
                    'scripts.js',
                ),
                'e81061cbad0d8b6fe328225d0df7dace.js',
            ),
        );
    }
}
