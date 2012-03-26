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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Core
 */
class Mage_Core_Model_Design_PackageTest extends PHPUnit_Framework_TestCase
{
    protected static $_pubJslib;

    protected static $_cssFiles = array(
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

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        $fixtureDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files';
        Mage::app()->getConfig()->getOptions()->setDesignDir($fixtureDir . DIRECTORY_SEPARATOR . 'design');
        Varien_Io_File::rmdirRecursive(Mage::app()->getConfig()->getOptions()->getMediaDir() . '/skin');
        self::$_pubJslib  = Mage::app()->getConfig()->getOptions()->getMediaDir() . '/js/';
        mkdir(self::$_pubJslib, 0777);
        copy($fixtureDir . '/skin/file.css', self::$_pubJslib . '/file.css');
        copy($fixtureDir . '/skin/script.js', self::$_pubJslib . '/script.js');
    }

    public static function tearDownAfterClass()
    {
        Varien_Io_File::rmdirRecursive(self::$_pubJslib);
    }

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Design_Package();
        $this->_model->setDesignTheme('test/default/default', 'frontend');
    }

    public function testSetGetArea()
    {
        $this->assertEquals(Mage_Core_Model_Design_Package::DEFAULT_AREA, $this->_model->getArea());
        $this->_model->setArea('test');
        $this->assertEquals('test', $this->_model->getArea());
    }

    public function testGetPackageName()
    {
        $this->assertEquals('test', $this->_model->getPackageName());
    }

    public function testGetTheme()
    {
        $this->assertEquals('default', $this->_model->getTheme());
    }

    public function testGetSkin()
    {
        $this->assertEquals('default', $this->_model->getSkin());
    }

    public function testSetDesignTheme()
    {
        $this->_model->setDesignTheme('test/test/test', 'test');
        $this->assertEquals('test', $this->_model->getArea());
        $this->assertEquals('test', $this->_model->getPackageName());
        $this->assertEquals('test', $this->_model->getSkin());
        $this->assertEquals('test', $this->_model->getSkin());
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testSetDesignThemeException()
    {
        $this->_model->setDesignTheme('test/test');
    }

    public function testGetDesignTheme()
    {
        $this->assertEquals('test/default/default', $this->_model->getDesignTheme());
    }

    /**
     * @dataProvider getTemplateFilenameDataProvider
     */
    public function testGetTemplateFilename($file, $params)
    {
        $this->assertFileExists($this->_model->getTemplateFilename($file, $params));
    }

    /**
     * @return array
     */
    public function getTemplateFilenameDataProvider()
    {
        return array(
            array('theme_file.txt', array('_module' => 'Mage_Catalog')),
            array('Mage_Catalog::theme_file.txt', array()),
            array('Mage_Catalog::theme_file_with_2_dots..txt', array()),
            array('Mage_Catalog::theme_file.txt', array('_module' => 'Overriden_Module')),
            array('fallback.phtml', array('_package' => 'package', '_theme' => 'custom_theme')),
        );
    }
    /*
    public function testGetThemeLocaleFile()
    {
        $this->assertFileExists($this->_model->getThemeLocaleFile('translate.csv'));
        $this->assertFileExists($this->_model->getThemeLocaleFile('fallback.csv', array(
            '_package' => 'package', '_theme' => 'custom_theme'
        )));
    }
    */

    /**
     * @param string $skinFile
     * @param array $designParams
     * @param string $expectedFilename
     * @param string $locale locale code or null for default locale
     * @dataProvider getSkinFileDataProvider
     */
    public function testGetSkinFile($skinFile, array $designParams, $expectedFilename, $locale = null)
    {
        Mage::app()->getLocale()->setLocale($locale);
        $expectedFilename = str_replace('/', DIRECTORY_SEPARATOR, $expectedFilename);
        $designParams += array('_package' => 'package', '_theme' => 'custom_theme');

        $actualFilename = $this->_model->getSkinFile($skinFile, $designParams);
        $this->assertStringMatchesFormat($expectedFilename, $actualFilename);

        $this->assertFileExists($actualFilename);
    }

    public function getSkinFileDataProvider()
    {
        $prefix = '%s/design/frontend/package/';
        return array(
            'skin file inside theme' => array(
                'fixture_script.js',
                array('_skin' => 'theme_nested_skin'),
                $prefix . 'custom_theme/skin/theme_nested_skin/fixture_script.js',
            ),
            'localized skin file inside theme' => array(
                'fixture_script.js',
                array('_skin' => 'theme_nested_skin'),
                $prefix . 'custom_theme/skin/theme_nested_skin/locale/ru_RU/fixture_script.js',
                'ru_RU',
            ),
            'modular skin file inside theme' => array(
                'fixture_script.js',
                array('_skin' => 'theme_nested_skin', '_module' => 'Fixture_Module'),
                $prefix . 'custom_theme/skin/theme_nested_skin/Fixture_Module/fixture_script.js',
            ),
            'localized modular skin file inside theme' => array(
                'fixture_script.js',
                array('_skin' => 'theme_nested_skin', '_module' => 'Fixture_Module'),
                $prefix . 'custom_theme/skin/theme_nested_skin/locale/ru_RU/Fixture_Module/fixture_script.js',
                'ru_RU',
            ),
            'lib skin file inside theme' => array(
                'mage/jquery-no-conflict.js',
                array('_skin' => 'theme_nested_skin'),
                $prefix . 'custom_theme/skin/theme_nested_skin/mage/jquery-no-conflict.js',
            ),
            'primary theme fallback - same theme & default skin' => array(
                'fixture_script_two.js',
                array('_skin' => 'theme_nested_skin'),
                $prefix . 'custom_theme/skin/default/fixture_script_two.js',
            ),
            'secondary theme fallback - default theme & same skin' => array(
                'fixture_script_three.js',
                array('_skin' => 'theme_nested_skin'),
                $prefix . 'default/skin/theme_nested_skin/fixture_script_three.js',
            ),
            'final theme fallback - default theme & default skin' => array(
                'fixture_script_four.js',
                array('_skin' => 'theme_nested_skin'),
                $prefix . 'default/skin/default/fixture_script_four.js',
            ),
            'lib fallback' => array(
                'mage/jquery-no-conflict.js',
                array('_skin' => 'default'),
                '%s/pub/js/mage/jquery-no-conflict.js',
            ),
        );
    }

    /**
     * @dataProvider getSkinUrlDataProvider
     */
    public function testGetSkinUrl($file, $expectedName, $locale = null)
    {
        Mage::app()->getLocale()->setLocale($locale);
        $url = $this->_model->getSkinUrl($file);
        $this->assertStringEndsWith($expectedName, $url);
        $skinFile = $this->_model->getSkinFile($file);
        $this->assertFileExists($skinFile);
    }

    /**
     * @return array
     */
    public function getSkinUrlDataProvider()
    {
        return array(
            'theme file' => array(
                'css/styles.css',
                'skin/frontend/test/default/default/en_US/css/styles.css',
            ),
            'theme localized file' => array(
                'logo.gif',
                'skin/frontend/test/default/default/fr_FR/logo.gif',
                'fr_FR',
            ),
            'modular file' => array(
                'Module::favicon.ico',
                'skin/frontend/test/default/default/en_US/Module/favicon.ico',
            ),
            'lib file' => array(
                'varien/product.js',
                'http://localhost/pub/js/varien/product.js',
            ),
            'lib folder' => array(
                'varien',
                'http://localhost/pub/js/varien',
            )
        );
    }

    /**
     * @magentoConfigFixture default/design/theme/allow_skin_files_duplication 0
     * @dataProvider testGetSkinUrlNoFilesDuplicationDataProvider
     */
    public function testGetSkinUrlNoFilesDuplication($file, $expectedName, $locale = null)
    {
        $this->testGetSkinUrl($file, $expectedName, $locale);
    }

    /**
     * @return array
     */
    public function testGetSkinUrlNoFilesDuplicationDataProvider()
    {
        return array(
            'theme css file' => array(
                'css/styles.css',
                'skin/frontend/test/default/default/en_US/css/styles.css',
            ),
            'theme file' => array(
                'images/logo.gif',
                'skin/frontend/test/default/skin/default/images/logo.gif',
            ),
            'theme localized file' => array(
                'logo.gif',
                'skin/frontend/test/default/skin/default/locale/fr_FR/logo.gif',
                'fr_FR',
            ),
            /*
            'modular file' => array(
                'Mage_Page::favicon.ico',
                'skin/_module/Mage_Page/frontend/favicon.ico',
            ),
            */
        );
    }

    /**
     * @magentoConfigFixture default/design/theme/allow_skin_files_duplication 0
     */
    public function testGetSkinUrlNoFilesDuplicationWithCaching()
    {
        Mage::app()->getLocale()->setLocale('en_US');
        $skinParams = array('_package' => 'test', '_theme' => 'default', '_skin' => 'default');
        $cacheKey = 'frontend/test/default/default/en_US';
        Mage::app()->cleanCache();

        $skinFile = 'images/logo.gif';
        $this->_model->getSkinUrl($skinFile, $skinParams);
        $map = unserialize(Mage::app()->loadCache($cacheKey));
        $this->assertTrue(count($map) == 1);
        $this->assertStringEndsWith('logo.gif', (string)array_pop($map));

        $skinFile = 'images/logo_email.gif';
        $this->_model->getSkinUrl($skinFile, $skinParams);
        $map = unserialize(Mage::app()->loadCache($cacheKey));
        $this->assertTrue(count($map) == 2);
        $this->assertStringEndsWith('logo_email.gif', (string)array_pop($map));
    }

    /**
     * @param string $file
     * @expectedException Magento_Exception
     * @dataProvider getSkinUrlDataExceptionProvider
     */
    public function testGetSkinUrlException($file)
    {
        $this->_model->getSkinUrl($file);
    }

    /**
     * @return array
     */
    public function getSkinUrlDataExceptionProvider()
    {
        return array(
            'non-existing theme file'  => array('path/to/nonexisting-file.ext'),
            'non-existing module file' => array('Some_Module::path/to/nonexisting-file.ext'),
        );
    }


    /**
     * @param string $file
     * @expectedException Magento_Exception
     * @dataProvider findFileExceptionDataProvider
     */
    public function testFindFileException($file)
    {
        $this->_model->getTemplateFilename($file);
    }

    public function findFileExceptionDataProvider()
    {
        return array(
            array('::no_scope.ext'),
            array('./file.ext'),
            array('../file.ext'),
            array('dir/./file.ext'),
            array('dir/../file.ext'),
        );
    }

    /**
     * Materialization of skin files in development mode
     *
     * @param string $application
     * @param string $package
     * @param string $theme
     * @param string $skin
     * @param string $file
     * @dataProvider materializeSkinFileDataProvider
     */
    public function testMaterializeSkinFile($application, $package, $theme, $skin, $file)
    {
        // determine path where a materialized file is to be expected
        $expectedModule = false;
        $targetFile   = $file;
        if (false !== strpos($file, '::')) {
            $targetFile = explode('::', $file);
            $expectedModule = $targetFile[0];
            $targetFile = $targetFile[1];
        }
        $path = array(Mage::getBaseDir('media'), 'skin', $application, $package, $theme, $skin, 'en_US');
        if ($expectedModule) {
            $path[] = $expectedModule;
        }
        $path[] = $targetFile;
        $targetFile = implode(DIRECTORY_SEPARATOR, $path);

        // test doesn't make sense if the original file doesn't exist or the target file already exists
        $params = array(
            '_area'    => $application,
            '_package' => $package,
            '_theme'   => $theme,
            '_skin'    => $skin,
        );
        $originalFile = $this->_model->getSkinFile($file, $params);
        $this->assertFileExists($originalFile);

        // getSkinUrl() will trigger materialization in development mode
        $this->assertFileNotExists($targetFile, 'Please verify isolation from previous test(s).');
        $this->_model->getSkinUrl($file, $params);
        $this->assertFileExists($targetFile);

        // as soon as the files are materialized, they must have the same mtime as originals
        $this->assertEquals(filemtime($originalFile), filemtime($targetFile),
            "These files mtime must be equal: {$originalFile} / {$targetFile}"
        );
    }

    /**
     * @return array
     */
    public function materializeSkinFileDataProvider()
    {
        return array(
            array('frontend', 'test', 'default', 'default', 'images/logo_email.gif'),
            array('frontend', 'test', 'default', 'default', 'Mage_Page::favicon.ico'),
        );
    }

    /**
     * Materialization of CSS files located in the theme (development mode)
     */
    public function testMaterializeCssFileFromTheme()
    {
        $materializedDir = Mage::getBaseDir('media') . '/skin/frontend/package/default/theme/en_US';
        $this->assertFileNotExists($materializedDir, 'Please verify isolation from previous test(s).');
        $this->_model->getSkinUrl('css/file.css', array(
            '_package' => 'package',
            '_skin' => 'theme',
        ));
        foreach (self::$_cssFiles as $file) {
            $this->assertFileExists("{$materializedDir}/{$file}");
        }
        $this->assertFileNotExists("{$materializedDir}/absolute.gif");
        $this->assertFileNotExists(dirname($materializedDir) . '/access_violation.php');
    }

    /**
     * Materialization of CSS files located in the module
     * @dataProvider materializeCssFileFromModuleDataProvider
     */
    public function testMaterializeCssFileFromModule(
        $cssSkinFile, $designParams, $expectedCssFile, $expectedCssContent, $expectedRelatedFiles
    ) {
        $baseDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 'skin' . DIRECTORY_SEPARATOR;

        $this->_model->getSkinUrl($cssSkinFile, $designParams);

        $expectedCssFile = $baseDir . $expectedCssFile;
        $this->assertFileExists($expectedCssFile);
        $actualCssContent = file_get_contents($expectedCssFile);

        $this->assertNotRegExp(
            '/url\(.*?' . Mage_Core_Model_Design_Package::SCOPE_SEPARATOR . '.*?\)/',
            $actualCssContent,
            'Materialized CSS file must not contain scope separators in URLs.'
        );

        foreach ($expectedCssContent as $expectedCssSubstring) {
            $this->assertContains($expectedCssSubstring, $actualCssContent);
        }

        foreach ($expectedRelatedFiles as $expectedFile) {
            $expectedFile = $baseDir . '/' . $expectedFile;
            $this->assertFileExists($expectedFile);
        }
    }

    public function materializeCssFileFromModuleDataProvider()
    {
        return array(
            'frontend' => array(
                'widgets.css',
                array(
                    '_area'    => 'frontend',
                    '_package' => 'default',
                    '_skin'    => 'default',
                    '_module'  => 'Mage_Reports',
                ),
                'frontend/default/default/default/en_US/Mage_Reports/widgets.css',
                array(
                    'url(../Mage_Catalog/images/i_block-list.gif)',
                ),
                array(
                    'frontend/default/default/default/en_US/Mage_Catalog/images/i_block-list.gif',
                ),
            ),
            'adminhtml' => array(
                'Mage_Paypal::boxes.css',
                array(
                    '_area'    => 'adminhtml',
                    '_package' => 'package',
                    '_theme'   => 'test',
                    '_skin'    => 'default',
                    '_module'  => false,
                ),
                'adminhtml/package/test/default/en_US/Mage_Paypal/boxes.css',
                array(
                    'url(logo.gif)',
                    'url(section.png)',
                ),
                array(
                    'adminhtml/package/test/default/en_US/Mage_Paypal/logo.gif',
                    'adminhtml/package/test/default/en_US/Mage_Paypal/section.png',
                ),
            ),
        );
    }

    public function testGetOptimalCssUrls()
    {
        $expected = array(
            'http://localhost/pub/media/skin/frontend/test/default/default/en_US/css/styles.css',
            'http://localhost/pub/js/calendar/calendar-blue.css',
        );
        $params = array(
            'css/styles.css',
            'calendar/calendar-blue.css',
        );
        $this->assertEquals($expected, $this->_model->getOptimalCssUrls($params));
    }

    /**
     * @param array $files
     * @param array $expectedFiles
     * @dataProvider getOptimalCssUrlsMergedDataProvider
     * @magentoConfigFixture current_store dev/css/merge_css_files 1
     */
    public function testGetOptimalCssUrlsMerged($files, $expectedFiles)
    {
        $this->assertEquals($expectedFiles, $this->_model->getOptimalCssUrls($files));
    }

    public function getOptimalCssUrlsMergedDataProvider()
    {
        return array(
            array(
                array('css/styles.css', 'calendar/calendar-blue.css'),
                array('http://localhost/pub/media/skin/_merged/5594035976651f0a40d65ed577700fb5.css')
            ),
            array(
                array('css/styles.css'),
                array('http://localhost/pub/media/skin/frontend/test/default/default/en_US/css/styles.css',)
            ),
        );
    }


    public function testGetOptimalJsUrls()
    {
        $expected = array(
            'http://localhost/pub/media/skin/frontend/test/default/default/en_US/js/tabs.js',
            'http://localhost/pub/js/calendar/calendar.js',
        );
        $params = array(
            'js/tabs.js',
            'calendar/calendar.js',
        );
        $this->assertEquals($expected, $this->_model->getOptimalJsUrls($params));
    }

    /**
     * @param array $files
     * @param array $expectedFiles
     * @dataProvider getOptimalJsUrlsMergedDataProvider
     * @magentoConfigFixture current_store dev/js/merge_files 1
     */
    public function testGetOptimalJsUrlsMerged($files, $expectedFiles)
    {
        $this->assertEquals($expectedFiles, $this->_model->getOptimalJsUrls($files));
    }

    public function getOptimalJsUrlsMergedDataProvider()
    {
        return array(
            array(
                array('js/tabs.js', 'calendar/calendar.js'),
                array('http://localhost/pub/media/skin/_merged/c5a9f4afba4ff0ff979445892214fc8b.js',)
            ),
            array(
                array('calendar/calendar.js'),
                array('http://localhost/pub/js/calendar/calendar.js',)
            ),
        );
    }

    public function testGetDesignEntitiesStructure()
    {
        $expectedResult = array(
            'package_one' => array(
                'theme_one' => array(
                    'skin_one' => true,
                    'skin_two' => true
                )
            )
        );
        $this->assertSame($expectedResult, $this->_model->getDesignEntitiesStructure('design_area'));
    }

    public function testGetThemeConfig()
    {
        $frontend = $this->_model->getThemeConfig('frontend');
        $this->assertInstanceOf('Magento_Config_Theme', $frontend);
        $this->assertSame($frontend, $this->_model->getThemeConfig('frontend'));
    }

    public function testIsThemeCompatible()
    {
        $this->assertFalse($this->_model->isThemeCompatible('frontend', 'package', 'custom_theme', '1.0.0.0'));
        $this->assertTrue($this->_model->isThemeCompatible('frontend', 'package', 'custom_theme', '2.0.0.0'));
    }

    public function testGetViewConfig()
    {
        $config = $this->_model->getViewConfig();
        $this->assertInstanceOf('Magento_Config_View', $config);
        $this->assertEquals(array('var1' => 'value1', 'var2' => 'value2'), $config->getVars('Namespace_Module'));
    }
}
