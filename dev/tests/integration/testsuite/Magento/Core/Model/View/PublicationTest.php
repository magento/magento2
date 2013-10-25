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
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\View;

class PublicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\View\DesignInterface
     */
    protected $_model;

    /**
     * @var \Magento\Core\Model\View\Service
     */
    protected $_viewService;

    /**
     * @var \Magento\Core\Model\View\FileSystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Core\Model\View\Url
     */
    protected $_viewUrl;

    protected function setUp()
    {
        $this->_viewService = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\Service');
        $this->_fileSystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\FileSystem');
        $this->_viewUrl = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\Url');
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\View\DesignInterface');
    }

    protected function tearDown()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Filesystem');
        $publicDir = $this->_viewService->getPublicDir();
        $filesystem->delete($publicDir . '/adminhtml');
        $filesystem->delete($publicDir . '/frontend');
        $this->_model = null;
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetPublicDir()
    {
        /** @var $dirs \Magento\App\Dir */
        $dirs = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\Dir');
        $expectedPublicDir = $dirs->getDir(\Magento\App\Dir::STATIC_VIEW);
        $this->assertEquals($expectedPublicDir, $this->_viewService->getPublicDir());
    }

    /**
     * Test that URL for a view file meets expectations
     *
     * @param string $file
     * @param string $expectedUrl
     * @param string|null $locale
     * @param bool|null $allowDuplication
     */
    protected function _testGetViewUrl($file, $expectedUrl, $locale = null, $allowDuplication = null)
    {
        $this->_initTestTheme($allowDuplication);

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\LocaleInterface')
            ->setLocale($locale);
        $url = $this->_viewUrl->getViewFileUrl($file);
        $this->assertStringEndsWith($expectedUrl, $url);
        $viewFile = $this->_fileSystem->getViewFile($file);
        $this->assertFileExists($viewFile);
    }

    /**
     * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider getViewUrlFilesDuplicationDataProvider
     */
    public function testGetViewUrlFilesDuplication($file, $expectedUrl, $locale = null)
    {
        $this->_testGetViewUrl($file, $expectedUrl, $locale, true);
    }

    /**
     * @return array
     */
    public function getViewUrlFilesDuplicationDataProvider()
    {
        return array(
            'theme file' => array(
                'css/styles.css',
                'static/frontend/test_default/en_US/css/styles.css',
            ),
            'theme localized file' => array(
                'logo.gif',
                'static/frontend/test_default/fr_FR/logo.gif',
                'fr_FR',
            ),
            'modular file' => array(
                'Namespace_Module::favicon.ico',
                'static/frontend/test_default/en_US/Namespace_Module/favicon.ico',
            ),
            'lib folder' => array(
                'varien',
                'http://localhost/pub/lib/varien',
            )
        );
    }

    /**
     * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider testGetViewUrlNoFilesDuplicationDataProvider
     */
    public function testGetViewUrlNoFilesDuplication($file, $expectedUrl, $locale = null)
    {
        $this->_testGetViewUrl($file, $expectedUrl, $locale, false);
    }

    /**
     * @return array
     */
    public function testGetViewUrlNoFilesDuplicationDataProvider()
    {
        return array(
            'theme css file' => array(
                'css/styles.css',
                'static/frontend/test_default/en_US/css/styles.css',
            ),
            'theme file' => array(
                'images/logo.gif',
                'static/frontend/test_default/images/logo.gif',
            ),
            'theme localized file' => array(
                'logo.gif',
                'static/frontend/test_default/i18n/fr_FR/logo.gif',
                'fr_FR',
            )
        );
    }

    /**
     * @expectedException \Magento\Exception
     * @dataProvider getViewUrlExceptionDataProvider
     */
    public function testGetViewUrlException($file)
    {
        $this->_viewUrl->getViewFileUrl($file);
    }

    /**
     * @return array
     */
    public function getViewUrlExceptionDataProvider()
    {
        return array(
            'non-existing theme file'  => array('path/to/non-existing-file.ext'),
            'non-existing module file' => array('Some_Module::path/to/non-existing-file.ext'),
        );
    }

    /**
     * Test on vulnerability for protected files
     *
     * @expectedException \Magento\Exception
     * @expectedExceptionMessage because it does not reside in a public directory
     * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider getProtectedFiles
     * @param array $designParams
     * @param string $filePath
     */
    public function testTemplatePublicationVulnerability($designParams, $filePath)
    {
        $this->_initTestTheme();
        $this->_viewUrl->getViewFileUrl($filePath, $designParams);
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
                array('area' => 'frontend', 'theme' => 'vendor_default'),
                'access_violation.php'
            ),
            array(
                array('area' => 'frontend', 'theme' => 'vendor_default'),
                'theme.xml'
            ),
            array(
                array('area' => 'frontend', 'theme' => 'test_default', 'module' => 'Magento_Catalog'),
                'catalog_category_view.xml'
            ),
            array(
                array('area' => 'frontend', 'theme' => 'test_default', 'module' => 'Magento_Core'),
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
     * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider getPublicFilePathDataProvider
     */
    public function testGetPublicFilePath($file, $designParams, $expectedFile)
    {
        $this->_initTestTheme();

        $expectedFile = $this->_viewService->getPublicDir() . '/' . $expectedFile;

        // test doesn't make sense if the original file doesn't exist or the target file already exists
        $originalFile = $this->_fileSystem->getViewFile($file, $designParams);
        $this->assertFileExists($originalFile);

        // getViewUrl() will trigger publication in development mode
        $this->assertFileNotExists($expectedFile, 'Please verify isolation from previous test(s).');
        $this->_viewUrl->getViewFileUrl($file, $designParams);
        $this->assertFileExists($expectedFile);

        // as soon as the files are published, they must have the same mtime as originals
        $this->assertEquals(filemtime($originalFile), filemtime($expectedFile),
            "These files mtime must be equal: {$originalFile} / {$expectedFile}"
        );
    }

    /**
     * @return array
     */
    public function getPublicFilePathDataProvider()
    {
        $designParams = array(
            'area'    => 'frontend',
            'theme'   => 'test_default',
            'locale'  => 'en_US'
        );
        return array(
            'view file' => array(
                'images/logo_email.gif',
                $designParams,
                'frontend/test_default/en_US/images/logo_email.gif',
            ),
            'view modular file' => array(
                'Magento_Page::favicon.ico',
                $designParams,
                'frontend/test_default/en_US/Magento_Page/favicon.ico',
            ),
        );
    }

    /**
     * Publication of CSS files located in the theme (development mode)
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
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
            'Magento_Page/favicon.ico', // non-fixture file from real module
        );
        $publishedDir = $this->_viewService->getPublicDir() . '/frontend/vendor_default/en_US';
        $this->assertFileNotExists($publishedDir, 'Please verify isolation from previous test(s).');
        $this->_viewUrl->getViewFileUrl('css/file.css', array(
            'theme'   => 'vendor_default',
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
     * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
     * @dataProvider publishCssFileFromModuleDataProvider
     */
    public function testPublishCssFileFromModule(
        $cssViewFile, $designParams, $expectedCssFile, $expectedCssContent, $expectedRelatedFiles
    ) {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
            ->getArea(\Magento\Core\Model\App\Area::AREA_FRONTEND)->load();
        $this->_viewUrl->getViewFileUrl($cssViewFile, $designParams);

        $expectedCssFile = $this->_viewService->getPublicDir() . '/' . $expectedCssFile;
        $this->assertFileExists($expectedCssFile);
        $actualCssContent = file_get_contents($expectedCssFile);

        $this->assertNotRegExp(
            '/url\(.*?' . \Magento\Core\Model\View\Service::SCOPE_SEPARATOR . '.*?\)/',
            $actualCssContent,
            'Published CSS file must not contain scope separators in URLs.'
        );

        foreach ($expectedCssContent as $expectedCssSubstring) {
            $this->assertContains($expectedCssSubstring, $actualCssContent);
        }

        foreach ($expectedRelatedFiles as $expectedFile) {
            $expectedFile = $this->_viewService->getPublicDir() . '/' . $expectedFile;
            $this->assertFileExists($expectedFile);
        }
    }

    /**
     * @return array
     */
    public function publishCssFileFromModuleDataProvider()
    {
        return array(
            'frontend' => array(
                'product/product.css',
                array(
                    'area'    => 'adminhtml',
                    'theme'   => 'magento_backend',
                    'locale'  => 'en_US',
                    'module'  => 'Magento_Catalog',
                ),
                'adminhtml/magento_backend/en_US/Magento_Catalog/product/product.css',
                array(
                    'url(../../Magento_Backend/images/gallery-image-base-label.png)',
                ),
                array(
                    'adminhtml/magento_backend/en_US/Magento_Backend/images/gallery-image-base-label.png',
                ),
            ),
            'adminhtml' => array(
                'Magento_Paypal::styles.css',
                array(
                    'area'    => 'adminhtml',
                    'theme'   => 'vendor_test',
                    'locale'  => 'en_US',
                    'module'  => false,
                ),
                'adminhtml/vendor_test/en_US/Magento_Paypal/styles.css',
                array(
                    'url(images/paypal-logo.png)',
                    'url(images/pp-allinone.png)',
                ),
                array(
                    'adminhtml/vendor_test/en_US/Magento_Paypal/images/paypal-logo.png',
                    'adminhtml/vendor_test/en_US/Magento_Paypal/images/pp-allinone.png',
                ),
            ),
        );
    }

    /**
     * Test that modified CSS file and changed resources are re-published in developer mode
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Core/_files/media_for_change.php
     */
    public function testPublishResourcesAndCssWhenChangedCssDevMode()
    {
        $mode = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State')
            ->getMode();
        if ($mode != \Magento\App\State::MODE_DEVELOPER) {
            $this->markTestSkipped('Valid in developer mode only');
        }
        $this->_testPublishResourcesAndCssWhenChangedCss(true);
    }

    /**
     * Test that modified CSS file and changed resources are not re-published in usual mode
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Core/_files/media_for_change.php
     */
    public function testNotPublishResourcesAndCssWhenChangedCssUsualMode()
    {
        $mode = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State')
            ->getMode();
        if ($mode == \Magento\App\State::MODE_DEVELOPER) {
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
        $appInstallDir = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInstallDir();
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(array(
            \Magento\Core\Model\App::PARAM_APP_DIRS => array(
                \Magento\App\Dir::THEMES => "$appInstallDir/media_for_change",
            )
        ));

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\View\DesignInterface');
        $this->_model->setDesignTheme('test_default');

        $this->_viewService = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\Service');
        $this->_fileSystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\FileSystem');
        $this->_viewUrl = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\Url');

        $themePath = $this->_model->getDesignTheme()->getFullPath();
        $fixtureViewPath = "$appInstallDir/media_for_change/$themePath/";
        $publishedPath = $this->_viewService->getPublicDir() . "/$themePath/en_US/";

        $this->_viewUrl->getViewFileUrl('style.css', array('locale' => 'en_US'));

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
        $this->_viewUrl->getViewFileUrl('style.css', array('locale' => 'en_US'));

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
     * @magentoDataFixture Magento/Core/_files/media_for_change.php
     * @magentoAppIsolation enabled
     */
    public function testPublishChangedResourcesWhenUnchangedCssDevMode()
    {
        $mode = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State')
            ->getMode();
        if ($mode != \Magento\App\State::MODE_DEVELOPER) {
            $this->markTestSkipped('Valid in developer mode only');
        }

        $this->_testPublishChangedResourcesWhenUnchangedCss(true);
    }

    /**
     * Test changed resources, referenced in non-modified CSS file, are re-published
     *
     * @magentoDataFixture Magento/Core/_files/media_for_change.php
     * @magentoAppIsolation enabled
     */
    public function testNotPublishChangedResourcesWhenUnchangedCssUsualMode()
    {
        $mode = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State')
            ->getMode();
        if ($mode == \Magento\App\State::MODE_DEVELOPER) {
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
        $appInstallDir = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInstallDir();
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(array(
            \Magento\Core\Model\App::PARAM_APP_DIRS => array(
                \Magento\App\Dir::THEMES => "$appInstallDir/media_for_change",
            )
        ));

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\View\DesignInterface');
        $this->_model->setDesignTheme('test_default');

        $this->_viewService = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\Service');
        $this->_fileSystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\FileSystem');
        $this->_viewUrl = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\Url');

        $themePath = $this->_model->getDesignTheme()->getFullPath();
        $fixtureViewPath = "$appInstallDir/media_for_change/$themePath/";
        $publishedPath = $this->_viewService->getPublicDir() . "/$themePath/en_US/";

        $this->_viewUrl->getViewFileUrl('style.css', array('locale' => 'en_US'));

        // Change referenced files
        copy($fixtureViewPath . 'images/rectangle.gif', $fixtureViewPath . 'images/square.gif');
        touch($fixtureViewPath . 'images/square.gif');
        file_put_contents(
            $fixtureViewPath . 'sub.css',
            '.sub2 {border: 1px solid magenta}',
            FILE_APPEND
        );

        $this->_viewUrl->getViewFileUrl('style.css', array('locale' => 'en_US'));

        $assertFileComparison = $expectedPublished ? 'assertFileEquals' : 'assertFileNotEquals';
        $this->$assertFileComparison($fixtureViewPath . 'sub.css', $publishedPath . 'sub.css');
        $this->$assertFileComparison($fixtureViewPath . 'images/rectangle.gif', $publishedPath . 'images/square.gif');
    }

    /**
     * Init the model with a test theme from fixture themes dir
     * Init application with custom view dir, @magentoAppIsolation required
     *
     * @param bool|null $allowDuplication
     */
    protected function _initTestTheme($allowDuplication = null)
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(array(
            \Magento\Core\Model\App::PARAM_APP_DIRS => array(
                \Magento\App\Dir::THEMES => dirname(__DIR__) . '/_files/design/'
            )
        ));

        if ($allowDuplication !== null) {
            $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
            $publisher = $objectManager->create(
                'Magento\Core\Model\View\Publisher',
                array('allowFilesDuplication' => $allowDuplication)
            );
            $objectManager->addSharedInstance($publisher, 'Magento\Core\Model\View\Publisher');
        }

        // Reinit model with new directories
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\View\DesignInterface');
        $this->_model->setDesignTheme('test_default');

        $this->_viewService = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\Service');
        $this->_fileSystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\FileSystem');
        $this->_viewUrl = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\View\Url');
    }

    /**
     * Check that the mechanism of publication not affected data content on css files
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCssWithBase64Data()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(array(
            \Magento\Core\Model\App::PARAM_APP_DIRS => array(
                \Magento\App\Dir::THEMES => dirname(__DIR__) . '/_files/design/'
            )
        ));
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')->loadAreaPart(
            \Magento\Core\Model\App\Area::AREA_ADMINHTML,
            \Magento\Core\Model\App\Area::PART_CONFIG
        );

        /** @var $themeCollection \Magento\Core\Model\Theme\Collection */
        $themeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Theme\Collection');
        $theme = $themeCollection->setBaseDir(dirname(__DIR__) . '/_files/design/')
            ->addTargetPattern(implode(DIRECTORY_SEPARATOR, array('frontend', 'vendor_default', 'theme.xml')))
            ->getFirstItem()
            ->save();

        $publishedPath = $this->_viewService->getPublicDir() . '/frontend/vendor_default/en_US';
        $params =  array(
            'area'    => 'frontend',
            'theme'   => 'vendor_default',
            'locale'  => 'en_US',
            'themeModel' => $theme
        );
        $filePath = $this->_fileSystem->getViewFile('css/base64.css', $params);

        // publish static content
        $this->_viewUrl->getViewFileUrl('css/base64.css', $params);
        $this->assertFileEquals($filePath, str_replace('/', DIRECTORY_SEPARATOR, "{$publishedPath}/css/base64.css"));

        $this->_model->setDesignTheme(\Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\View\Design\ThemeInterface'));
    }

    /**
     * Publication of view files in development mode
     *
     * @param string $file
     * @param $designParams
     * @param string $expectedFile
     * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider getPublicFilePathDataProvider
     */
    public function testGetViewFilePublicPath($file, $designParams, $expectedFile)
    {
        $this->_initTestTheme();

        $expectedFile = $this->_viewService->getPublicDir() . '/' . $expectedFile;

        $this->assertFileNotExists($expectedFile, 'Please verify isolation from previous test(s).');
        $this->_viewUrl->getViewFilePublicPath($file, $designParams);
        $this->assertFileExists($expectedFile);
    }

    public function testGetViewFilePublicPathExistingFile()
    {
        $filePath = 'mage/mage.js';
        $expectedFile = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\Dir')
                ->getDir(\Magento\App\Dir::PUB_LIB) . '/' . $filePath;
        $this->assertFileExists($expectedFile, 'Please verify existence of public library file');

        $actualFile = $this->_viewUrl->getViewFilePublicPath($filePath);
        $this->assertFileEquals($expectedFile, $actualFile);

        // Nothing must have been published
        $this->assertEmpty(glob($this->_viewService->getPublicDir() . '/*'));
    }
}
