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

class Mage_Core_Model_Design_PackagePublicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Core_Model_Design_Package');
    }

    protected function tearDown()
    {
        $filesystem = Mage::getObjectManager()->create('Magento_Filesystem');
        $publicDir = $this->_model->getPublicDir();
        $filesystem->delete($publicDir . '/adminhtml');
        $filesystem->delete($publicDir . '/frontend');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetPublicDir()
    {
        /** @var $dirs Mage_Core_Model_Dir */
        $dirs = Mage::getObjectManager()->get('Mage_Core_Model_Dir');
        $expectedPublicDir = $dirs->getDir(Mage_Core_Model_Dir::THEME) . DIRECTORY_SEPARATOR
            . Mage_Core_Model_Design_Package::PUBLIC_BASE_THEME_DIR;
        $this->assertEquals($expectedPublicDir, $this->_model->getPublicDir());
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
        $this->_initTestTheme();

        Mage::app()->getLocale()->setLocale($locale);
        $url = $this->_model->getViewFileUrl($file);
        $this->assertStringEndsWith($expectedUrl, $url);
        $viewFile = $this->_model->getViewFile($file);
        $this->assertFileExists($viewFile);
    }

    /**
     * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
     * @magentoConfigFixture global/design/theme/allow_view_files_duplication 1
     * @magentoAppIsolation enabled
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
                'theme/static/frontend/test/default/en_US/css/styles.css',
            ),
            'theme localized file' => array(
                'logo.gif',
                'theme/static/frontend/test/default/fr_FR/logo.gif',
                'fr_FR',
            ),
            'modular file' => array(
                'Module::favicon.ico',
                'theme/static/frontend/test/default/en_US/Module/favicon.ico',
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
     * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
     * @magentoConfigFixture global/design/theme/allow_view_files_duplication 0
     * @magentoAppIsolation enabled
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
                'theme/static/frontend/test/default/en_US/css/styles.css',
            ),
            'theme file' => array(
                'images/logo.gif',
                'theme/static/frontend/test/default/images/logo.gif',
            ),
            'theme localized file' => array(
                'logo.gif',
                'theme/static/frontend/test/default/locale/fr_FR/logo.gif',
                'fr_FR',
            )
        );
    }

    /**
     * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
     * @magentoConfigFixture global/design/theme/allow_view_files_duplication 0
     * @magentoAppIsolation enabled
     */
    public function testGetViewUrlNoFilesDuplicationWithCaching()
    {
        $this->_initTestTheme();
        $this->_model->setDesignTheme('test/default');
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
     * @expectedExceptionMessage because it does not reside in a public directory
     * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider getProtectedFiles
     * @param array $designParams
     * @param string $filePath
     */
    public function testTemplatePublicationVulnerability($designParams, $filePath)
    {
        $this->_initTestTheme();
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
     * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider publishViewFileDataProvider
     */
    public function testPublishViewFile($file, $designParams, $expectedFile)
    {
        $this->_initTestTheme();

        $expectedFile = $this->_model->getPublicDir() . '/' . $expectedFile;

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
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
     */
    public function testPublishCssFileFromTheme()
    {
        $this->_initTestTheme();
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
        $publishedDir = $this->_model->getPublicDir() . '/frontend/package/default/en_US';
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
     *
     * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
     * @magentoDataFixture Mage/Core/_files/frontend_default_theme.php
     * @dataProvider publishCssFileFromModuleDataProvider
     */
    public function testPublishCssFileFromModule(
        $cssViewFile, $designParams, $expectedCssFile, $expectedCssContent, $expectedRelatedFiles
    ) {
        $this->_model->getViewFileUrl($cssViewFile, $designParams);

        $expectedCssFile = $this->_model->getPublicDir() . '/' . $expectedCssFile;
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
            $expectedFile = $this->_model->getPublicDir() . '/' . $expectedFile;
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
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/_files/media_for_change.php
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
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/_files/media_for_change.php
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
        $appInstallDir = Magento_Test_Helper_Bootstrap::getInstance()->getAppInstallDir();
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
            Mage_Core_Model_App::INIT_OPTION_DIRS => array(
                Mage_Core_Model_Dir::THEMES => "$appInstallDir/media_for_change",
            )
        ));
        $this->_model->setDesignTheme('test/default');
        $themePath = $this->_model->getDesignTheme()->getFullPath();
        $fixtureViewPath = "$appInstallDir/media_for_change/$themePath/";
        $publishedPath = $this->_model->getPublicDir() . "/$themePath/en_US/";

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
     *
     * @magentoDataFixture Mage/Core/_files/media_for_change.php
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
     *
     * @magentoDataFixture Mage/Core/_files/media_for_change.php
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
        $appInstallDir = Magento_Test_Helper_Bootstrap::getInstance()->getAppInstallDir();
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
            Mage_Core_Model_App::INIT_OPTION_DIRS => array(
                Mage_Core_Model_Dir::THEMES => "$appInstallDir/media_for_change",
            )
        ));
        $this->_model->setDesignTheme('test/default');
        $themePath = $this->_model->getDesignTheme()->getFullPath();
        $fixtureViewPath = "$appInstallDir/media_for_change/$themePath/";
        $publishedPath = $this->_model->getPublicDir() . "/$themePath/en_US/";

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
     * Init the model with a test theme from fixture themes dir
     * Init application with custom view dir, @magentoAppIsolation required
     */
    protected function _initTestTheme()
    {
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
            Mage_Core_Model_App::INIT_OPTION_DIRS => array(
                Mage_Core_Model_Dir::THEMES => dirname(__DIR__) . '/_files/design/'
            )
        ));
        $this->_model->setDesignTheme('test/default');
    }

    /**
     * Check that the mechanism of publication not affected data content on css files
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCssWithBase64Data()
    {
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
            Mage_Core_Model_App::INIT_OPTION_DIRS => array(
                Mage_Core_Model_Dir::THEMES => dirname(__DIR__) . '/_files/design/'
            )
        ));

        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $themePath = implode(DS, array('frontend', 'package', 'default', 'theme.xml'));

        $theme = $themeModel->getCollectionFromFilesystem()
            ->setBaseDir(dirname(__DIR__) . '/_files/design/')
            ->addTargetPattern($themePath)
            ->getFirstItem()
            ->save();

        $publishedPath = $this->_model->getPublicDir() . '/frontend/package/default/en_US';
        $params =  array(
            'area'    => 'frontend',
            'package' => 'package',
            'theme'   => 'default',
            'locale'  => 'en_US',
            'themeModel' => $theme
        );
        $filePath = $this->_model->getViewFile('css/base64.css', $params);

        // publicate static content
        $this->_model->getViewFileUrl('css/base64.css', $params);
        $this->assertFileEquals($filePath, str_replace('/', DIRECTORY_SEPARATOR, "{$publishedPath}/css/base64.css"));

        $this->_model->setDesignTheme(Mage::getModel('Mage_Core_Model_Theme'));
    }
}
