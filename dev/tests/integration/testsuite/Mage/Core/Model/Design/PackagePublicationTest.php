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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoDbIsolation enabled
 */
class Mage_Core_Model_Design_PackagePublicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Path to the public directory for view files
     *
     * @var string
     */
    protected static $_themePublicDir;

    /**
     * Path for temporary fixture files. Used to test publishing changed files.
     *
     * @var string
     */
    protected static $_fixtureTmpDir;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        self::$_themePublicDir = Mage::app()->getConfig()->getOptions()->getMediaDir() . '/theme';
        self::$_fixtureTmpDir = Magento_Test_Bootstrap::getInstance()->getTmpDir() . '/publication';
    }

    protected function setUp()
    {
        /** @var $themeUtility Mage_Core_Utility_Theme */
        $themeUtility = Mage::getModel('Mage_Core_Utility_Theme', array(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'design',
            Mage::getModel('Mage_Core_Model_Design_Package')
        ));
        $themeUtility->registerThemes()->setDesignTheme('test/default', 'frontend');
        $this->_model = $themeUtility->getDesign();
    }

    protected function tearDown()
    {
        Varien_Io_File::rmdirRecursive(self::$_themePublicDir);
        Varien_Io_File::rmdirRecursive(self::$_fixtureTmpDir);
        $this->_model = null;
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetPublicThemeDir()
    {
        Mage::app()->getConfig()->getOptions()->setMediaDir(__DIR__);
        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'theme', $this->_model->getPublicDir());
    }

    /**
     * Test that URL for a view file meets expectations
     *
     * @param string $file
     * @param string $expectedUrl
     * @param string|null $locale
     */
    protected function _testGetViewUrl($file, $expectedUrl, $locale = null)
    {
        Mage::app()->getLocale()->setLocale($locale);
        $url = $this->_model->getViewFileUrl($file);
        $this->assertStringEndsWith($expectedUrl, $url);
        $viewFile = $this->_model->getViewFile($file);
        $this->assertFileExists($viewFile);
    }

    /**
     * @magentoConfigFixture default/design/theme/allow_view_files_duplication 1
     * @dataProvider getViewUrlFilesDuplicationDataProvider
     */
    public function testGetViewUrlFilesDuplication($file, $expectedUrl, $locale = null)
    {
        $this->_testGetViewUrl($file, $expectedUrl, $locale);
    }

    /**
     * @return array
     */
    public function getViewUrlFilesDuplicationDataProvider()
    {
        return array(
            'theme file' => array(
                'css/styles.css',
                'theme/frontend/test/default/en_US/css/styles.css',
            ),
            'theme localized file' => array(
                'logo.gif',
                'theme/frontend/test/default/fr_FR/logo.gif',
                'fr_FR',
            ),
            'modular file' => array(
                'Module::favicon.ico',
                'theme/frontend/test/default/en_US/Module/favicon.ico',
            ),
            'lib file' => array(
                'varien/product.js',
                'http://localhost/pub/lib/varien/product.js',
            ),
            'lib folder' => array(
                'varien',
                'http://localhost/pub/lib/varien',
            )
        );
    }

    /**
     * @magentoConfigFixture default/design/theme/allow_view_files_duplication 0
     * @dataProvider testGetViewUrlNoFilesDuplicationDataProvider
     */
    public function testGetViewUrlNoFilesDuplication($file, $expectedUrl, $locale = null)
    {
        $this->_testGetViewUrl($file, $expectedUrl, $locale);
    }

    /**
     * @return array
     */
    public function testGetViewUrlNoFilesDuplicationDataProvider()
    {
        return array(
            'theme css file' => array(
                'css/styles.css',
                'theme/frontend/test/default/en_US/css/styles.css',
            ),
            'theme file' => array(
                'images/logo.gif',
                'theme/frontend/test/default/images/logo.gif',
            ),
            'theme localized file' => array(
                'logo.gif',
                'theme/frontend/test/default/locale/fr_FR/logo.gif',
                'fr_FR',
            )
        );
    }

    /**
     * @magentoConfigFixture default/design/theme/allow_view_files_duplication 0
     */
    public function testGetViewUrlNoFilesDuplicationWithCaching()
    {
        Mage::app()->getLocale()->setLocale('en_US');
        $theme = $this->_model->getDesignTheme();
        $themeDesignParams = array('themeModel' => $theme);
        $cacheKey = "frontend|{$theme->getId()}|en_US";
        Mage::app()->cleanCache();

        $viewFile = 'images/logo.gif';
        $this->_model->getViewFileUrl($viewFile, $themeDesignParams);
        $map = unserialize(Mage::app()->loadCache($cacheKey));
        $this->assertTrue(count($map) == 1);
        $this->assertStringEndsWith('logo.gif', (string)array_pop($map));

        $viewFile = 'images/logo_email.gif';
        $this->_model->getViewFileUrl($viewFile, $themeDesignParams);
        $map = unserialize(Mage::app()->loadCache($cacheKey));
        $this->assertTrue(count($map) == 2);
        $this->assertStringEndsWith('logo_email.gif', (string)array_pop($map));
    }

    /**
     * @param string $file
     * @expectedException Magento_Exception
     * @dataProvider getViewUrlDataExceptionProvider
     */
    public function testGetViewUrlException($file)
    {
        $this->_model->getViewFileUrl($file);
    }

    /**
     * @return array
     */
    public function getViewUrlDataExceptionProvider()
    {
        return array(
            'non-existing theme file'  => array('path/to/non-existing-file.ext'),
            'non-existing module file' => array('Some_Module::path/to/non-existing-file.ext'),
        );
    }

    /**
     * Test on vulnerability for protected files
     *
     * @expectedException Magento_Exception
     * @dataProvider getProtectedFiles
     * @param array $designParams
     * @param string $filePath
     */
    public function testTemplatePublicationVulnerability($designParams, $filePath)
    {
        $this->_model->getViewFileUrl($filePath, $designParams);
    }

    /**
     * Return files, which are not published
     *
     * @return array
     */
    public function getProtectedFiles()
    {
        return array(
            array(
                array('area' => 'frontend', 'package' => 'package', 'theme' => 'default'),
                'access_violation.php'
            ),
            array(
                array('area' => 'frontend', 'package' => 'package', 'theme' => 'default'),
                'theme.xml'
            ),
            array(
                array('area' => 'frontend', 'package' => 'test', 'theme' => 'default', 'module' => 'Mage_Catalog'),
                'layout.xml'
            ),
            array(
                array('area' => 'frontend', 'package' => 'test', 'theme' => 'default', 'module' => 'Mage_Core'),
                'test.phtml'
            ),
        );
    }


    /**
     * Publication of view files in development mode
     *
     * @param string $file
     * @param $designParams
     * @param string $expectedFile
     * @dataProvider publishViewFileDataProvider
     */
    public function testPublishViewFile($file, $designParams, $expectedFile)
    {
        $expectedFile = self::$_themePublicDir . '/' . $expectedFile;

        // test doesn't make sense if the original file doesn't exist or the target file already exists
        $originalFile = $this->_model->getViewFile($file, $designParams);
        $this->assertFileExists($originalFile);

        // getViewUrl() will trigger publication in development mode
        $this->assertFileNotExists($expectedFile, 'Please verify isolation from previous test(s).');
        $this->_model->getViewFileUrl($file, $designParams);
        $this->assertFileExists($expectedFile);

        // as soon as the files are published, they must have the same mtime as originals
        $this->assertEquals(filemtime($originalFile), filemtime($expectedFile),
            "These files mtime must be equal: {$originalFile} / {$expectedFile}"
        );
    }

    /**
     * @return array
     */
    public function publishViewFileDataProvider()
    {
        $designParams = array(
            'area'    => 'frontend',
            'package' => 'test',
            'theme'   => 'default',
            'locale'  => 'en_US'
        );
        return array(
            'view file' => array(
                'images/logo_email.gif',
                $designParams,
                'frontend/test/default/en_US/images/logo_email.gif',
            ),
            'view modular file' => array(
                'Mage_Page::favicon.ico',
                $designParams,
                'frontend/test/default/en_US/Mage_Page/favicon.ico',
            ),
        );
    }

    /**
     * Publication of CSS files located in the theme (development mode)
     */
    public function testPublishCssFileFromTheme()
    {
        $expectedFiles = array(
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
        );
        $publishedDir = self::$_themePublicDir . '/frontend/package/default/en_US';
        $this->assertFileNotExists($publishedDir, 'Please verify isolation from previous test(s).');
        $this->_model->getViewFileUrl('css/file.css', array(
            'package' => 'package',
            'theme'   => 'default',
            'locale'  => 'en_US'
        ));
        foreach ($expectedFiles as $file) {
            $this->assertFileExists("{$publishedDir}/{$file}");
        }
        $this->assertFileNotExists("{$publishedDir}/absolute.gif");
    }

    /**
     * Publication of CSS files located in the module
     * @dataProvider publishCssFileFromModuleDataProvider
     */
    public function testPublishCssFileFromModule(
        $cssViewFile, $designParams, $expectedCssFile, $expectedCssContent, $expectedRelatedFiles
    ) {
        $this->_model->getViewFileUrl($cssViewFile, $designParams);

        $expectedCssFile = self::$_themePublicDir . '/' . $expectedCssFile;
        $this->assertFileExists($expectedCssFile);
        $actualCssContent = file_get_contents($expectedCssFile);

        $this->assertNotRegExp(
            '/url\(.*?' . Mage_Core_Model_Design_Package::SCOPE_SEPARATOR . '.*?\)/',
            $actualCssContent,
            'Published CSS file must not contain scope separators in URLs.'
        );

        foreach ($expectedCssContent as $expectedCssSubstring) {
            $this->assertContains($expectedCssSubstring, $actualCssContent);
        }

        foreach ($expectedRelatedFiles as $expectedFile) {
            $expectedFile = self::$_themePublicDir . '/' . $expectedFile;
            $this->assertFileExists($expectedFile);
        }
    }

    public function publishCssFileFromModuleDataProvider()
    {
        return array(
            'frontend' => array(
                'widgets.css',
                array(
                    'area'    => 'frontend',
                    'package' => 'default',
                    'theme'   => 'default',
                    'locale'  => 'en_US',
                    'module'  => 'Mage_Reports',
                ),
                'frontend/default/default/en_US/Mage_Reports/widgets.css',
                array(
                    'url(../Mage_Catalog/images/i_block-list.gif)',
                ),
                array(
                    'frontend/default/default/en_US/Mage_Catalog/images/i_block-list.gif',
                ),
            ),
            'adminhtml' => array(
                'Mage_Paypal::boxes.css',
                array(
                    'area'    => 'adminhtml',
                    'package' => 'package',
                    'theme'   => 'test',
                    'locale'  => 'en_US',
                    'module'  => false,
                ),
                'adminhtml/package/test/en_US/Mage_Paypal/boxes.css',
                array(
                    'url(logo.gif)',
                    'url(section.png)',
                ),
                array(
                    'adminhtml/package/test/en_US/Mage_Paypal/logo.gif',
                    'adminhtml/package/test/en_US/Mage_Paypal/section.png',
                ),
            ),
        );
    }


    /**
     * Test that modified CSS file and changed resources are re-published in developer mode
     */
    public function testPublishResourcesAndCssWhenChangedCssDevMode()
    {
        if (!Mage::getIsDeveloperMode()) {
            $this->markTestSkipped('Valid in developer mode only');
        }
        $this->_testPublishResourcesAndCssWhenChangedCss(true);
    }

    /**
     * Test that modified CSS file and changed resources are not re-published in usual mode
     */
    public function testNotPublishResourcesAndCssWhenChangedCssUsualMode()
    {
        if (Mage::getIsDeveloperMode()) {
            $this->markTestSkipped('Valid in non-developer mode only');
        }
        $this->_testPublishResourcesAndCssWhenChangedCss(false);
    }

    /**
     * Tests what happens when CSS file and its resources are changed - whether they are re-published or not
     *
     * @param bool $expectedPublished
     */
    protected function _testPublishResourcesAndCssWhenChangedCss($expectedPublished)
    {
        $fixtureViewPath = self::$_fixtureTmpDir . '/frontend/test/default/';
        $publishedPath = self::$_themePublicDir . '/frontend/test/default/en_US/';

        // Prepare temporary fixture directory and publish files from it
        $this->_copyFixtureViewToTmpDir($fixtureViewPath);
        $this->_model->getViewFileUrl('style.css', array('locale' => 'en_US'));

        // Change main file and referenced files - everything changed and referenced must appear
        file_put_contents(
            $fixtureViewPath . 'style.css',
            'div {background: url(images/rectangle.gif);}',
            FILE_APPEND
        );
        file_put_contents(
            $fixtureViewPath . 'sub.css',
            '.sub2 {border: 1px solid magenta}',
            FILE_APPEND
        );
        $this->_model->getViewFileUrl('style.css', array('locale' => 'en_US'));

        $assertFileComparison = $expectedPublished ? 'assertFileEquals' : 'assertFileNotEquals';
        $this->$assertFileComparison($fixtureViewPath . 'style.css', $publishedPath . 'style.css');
        $this->$assertFileComparison($fixtureViewPath . 'sub.css', $publishedPath . 'sub.css');
        if ($expectedPublished) {
            $this->assertFileEquals(
                $fixtureViewPath . 'images/rectangle.gif', $publishedPath . 'images/rectangle.gif'
            );
        } else {
            $this->assertFileNotExists($publishedPath . 'images/rectangle.gif');
        }
    }


    /**
     * Test changed resources, referenced in non-modified CSS file, are re-published
     * @magentoAppIsolation enabled
     */
    public function testPublishChangedResourcesWhenUnchangedCssDevMode()
    {
        if (!Mage::getIsDeveloperMode()) {
            $this->markTestSkipped('Valid in developer mode only');
        }

        $this->_testPublishChangedResourcesWhenUnchangedCss(true);
    }

    /**
     * Test changed resources, referenced in non-modified CSS file, are re-published
     * @magentoAppIsolation enabled
     */
    public function testNotPublishChangedResourcesWhenUnchangedCssUsualMode()
    {
        if (Mage::getIsDeveloperMode()) {
            $this->markTestSkipped('Valid in non-developer mode only');
        }

        $this->_testPublishChangedResourcesWhenUnchangedCss(false);
    }

    /**
     * Tests what happens when CSS file and its resources are changed - whether they are re-published or not
     *
     * @param bool $expectedPublished
     */
    protected function _testPublishChangedResourcesWhenUnchangedCss($expectedPublished)
    {
        $fixtureViewPath = self::$_fixtureTmpDir . '/frontend/test/default/';
        $publishedPath = self::$_themePublicDir . '/frontend/test/default/en_US/';

        // Prepare temporary fixture directory and publish files from it
        $this->_copyFixtureViewToTmpDir($fixtureViewPath);
        $this->_model->getViewFileUrl('style.css', array('locale' => 'en_US'));

        // Change referenced files
        copy($fixtureViewPath . 'images/rectangle.gif', $fixtureViewPath . 'images/square.gif');
        touch($fixtureViewPath . 'images/square.gif');
        file_put_contents(
            $fixtureViewPath . 'sub.css',
            '.sub2 {border: 1px solid magenta}',
            FILE_APPEND
        );

        $this->_model->getViewFileUrl('style.css', array('locale' => 'en_US'));

        $assertFileComparison = $expectedPublished ? 'assertFileEquals' : 'assertFileNotEquals';
        $this->$assertFileComparison($fixtureViewPath . 'sub.css', $publishedPath . 'sub.css');
        $this->$assertFileComparison($fixtureViewPath . 'images/rectangle.gif', $publishedPath . 'images/square.gif');
    }

    /**
     * Prepare design directory with initial css and resources
     *
     * @param string $fixtureViewPath
     */
    protected function _copyFixtureViewToTmpDir($fixtureViewPath)
    {
        Mage::app()->getConfig()->getOptions()->setDesignDir(self::$_fixtureTmpDir);
        mkdir($fixtureViewPath . '/images', 0777, true);

        // Copy all files to fixture location
        $mTime = time() - 10; // To ensure that all files, changed later in test, will be recognized for publication
        $sourcePath = dirname(__DIR__) . '/_files/design/frontend/test/publication/';
        $files = array('theme.xml', 'style.css', 'sub.css', 'images/square.gif', 'images/rectangle.gif');
        foreach ($files as $file) {
            copy($sourcePath . $file, $fixtureViewPath . $file);
            touch($fixtureViewPath . $file, $mTime);
        }
    }
}
