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
 * Tests for the view layer fallback mechanism
 */
class Mage_Core_Model_Design_PackageFallbackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        Mage::app()->getConfig()->getOptions()->setDesignDir(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'design'
        );
    }

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Design_Package();
        $this->_model->setDesignTheme('test/default/default', 'frontend');
    }

    /**
     * Test for the theme files fallback
     *
     * @param string $themeFile
     * @param array $designParams
     * @param string|null $expectedFilename
     *
     * @dataProvider getThemeFileFallbackDataProvider
     */
    public function testThemeFileFallback($themeFile, array $designParams, $expectedFilename)
    {
        $expectedFilename = str_replace('/', DIRECTORY_SEPARATOR, $expectedFilename);
        $actualFilename = $this->_model->getFilename($themeFile, $designParams);
        if ($expectedFilename) {
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFileNotExists($actualFilename);
        }
    }

    public function getThemeFileFallbackDataProvider()
    {
        return array(
            'no default theme inheritance' => array(
                'fixture_template.phtml',
                array('_package' => 'package', '_theme' => 'standalone_theme'),
                null
            ),
            'same package & parent theme' => array(
                'fixture_template_two.phtml',
                array('_package' => 'package', '_theme' => 'custom_theme_descendant'),
                "%s/frontend/package/custom_theme/fixture_template_two.phtml",
            ),
            'same package & grandparent theme' => array(
                'fixture_template.phtml',
                array('_package' => 'package', '_theme' => 'custom_theme_descendant'),
                "%s/frontend/package/default/fixture_template.phtml",
            ),
            'parent package & parent theme' => array(
                'fixture_template_two.phtml',
                array('_package' => 'test', '_theme' => 'external_package_descendant'),
                "%s/frontend/package/custom_theme/fixture_template_two.phtml",
            ),
            'parent package & grandparent theme' => array(
                'fixture_template.phtml',
                array('_package' => 'test', '_theme' => 'external_package_descendant'),
                "%s/frontend/package/default/fixture_template.phtml",
            ),
        );
    }

    /**
     * Test for the locale translation files fallback
     *
     * @param string $file
     * @param array $designParams
     * @param string|null $expectedFilename
     *
     * @dataProvider getLocaleFileFallbackDataProvider
     */
    public function testLocaleFileFallback($file, array $designParams, $expectedFilename)
    {
        $expectedFilename = str_replace('/', DIRECTORY_SEPARATOR, $expectedFilename);
        $actualFilename = $this->_model->getLocaleFileName($file, $designParams);
        if ($expectedFilename) {
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFileNotExists($actualFilename);
        }
    }

    public function getLocaleFileFallbackDataProvider()
    {
        return array(
            'no default theme inheritance' => array(
                'fixture_translate.csv',
                array('_package' => 'package', '_theme' => 'standalone_theme'),
                null
            ),
            'parent theme' => array(
                'fixture_translate_two.csv',
                array('_package' => 'package', '_theme' => 'custom_theme_descendant'),
                "%s/frontend/package/custom_theme/locale/en_US/fixture_translate_two.csv",
            ),
            'grandparent theme' => array(
                'fixture_translate.csv',
                array('_package' => 'package', '_theme' => 'custom_theme_descendant'),
                "%s/frontend/package/default/locale/en_US/fixture_translate.csv",
            ),
        );
    }

    /**
     * Test for the skin files fallback according to the themes inheritance
     *
     * @param string $skinFile
     * @param array $designParams
     * @param string|null $expectedFilename
     * @param string|null $locale
     *
     * @dataProvider skinFileFallbackDataProvider
     */
    public function testSkinFileFallback($skinFile, array $designParams, $expectedFilename, $locale = null)
    {
        Mage::app()->getLocale()->setLocale($locale);
        $expectedFilename = str_replace('/', DIRECTORY_SEPARATOR, $expectedFilename);
        $actualFilename = $this->_model->getSkinFile($skinFile, $designParams);
        if ($expectedFilename) {
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFileNotExists($actualFilename);
        }
    }

    public function skinFileFallbackDataProvider()
    {
        return array(
            'no default theme inheritance' => array(
                'fixture_script_two.js',
                array('_package' => 'package', '_theme' => 'standalone_theme', '_skin' => 'theme_nested_skin'),
                null,
            ),
            'same theme & default skin' => array(
                'fixture_script_two.js',
                array('_package' => 'package', '_theme' => 'custom_theme', '_skin' => 'theme_nested_skin'),
                "%s/frontend/package/custom_theme/skin/default/fixture_script_two.js",
            ),
            'parent theme & same skin' => array(
                'fixture_script.js',
                array('_package' => 'package', '_theme' => 'custom_theme_descendant', '_skin' => 'theme_nested_skin'),
                "%s/frontend/package/custom_theme/skin/theme_nested_skin/fixture_script.js",
            ),
            'parent theme & default skin' => array(
                'fixture_script_two.js',
                array('_package' => 'package', '_theme' => 'custom_theme_descendant', '_skin' => 'theme_nested_skin'),
                "%s/frontend/package/custom_theme/skin/default/fixture_script_two.js",
            ),
            'grandparent theme & same skin' => array(
                'fixture_script_three.js',
                array('_package' => 'package', '_theme' => 'custom_theme_descendant', '_skin' => 'theme_nested_skin'),
                "%s/frontend/package/default/skin/theme_nested_skin/fixture_script_three.js",
            ),
            'grandparent theme & default skin' => array(
                'fixture_script_four.js',
                array('_package' => 'package', '_theme' => 'custom_theme_descendant', '_skin' => 'theme_nested_skin'),
                "%s/frontend/package/default/skin/default/fixture_script_four.js",
            ),
            'parent package & same theme & same skin' => array(
                'fixture_script.js',
                array('_package' => 'test', '_theme' => 'external_package_descendant', '_skin' => 'theme_nested_skin'),
                "%s/frontend/package/custom_theme/skin/theme_nested_skin/fixture_script.js",
            ),
            'parent package & same theme & default skin' => array(
                'fixture_script_two.js',
                array('_package' => 'test', '_theme' => 'external_package_descendant', '_skin' => 'theme_nested_skin'),
                "%s/frontend/package/custom_theme/skin/default/fixture_script_two.js",
            ),
        );
    }

    /**
     * Test for the skin files localization
     *
     * @param string $skinFile
     * @param array $designParams
     * @param string|null $expectedFilename
     * @param string|null $locale
     *
     * @dataProvider skinFileL10nFallbackDataProvider
     */
    public function testSkinFileL10nFallback($skinFile, array $designParams, $expectedFilename, $locale = null)
    {
        $this->testSkinFileFallback($skinFile, $designParams, $expectedFilename, $locale);
    }

    public function skinFileL10nFallbackDataProvider()
    {
        return array(
            'general skin file' => array(
                'fixture_script.js',
                array('_package' => 'package', '_theme' => 'custom_theme', '_skin' => 'theme_nested_skin'),
                "%s/frontend/package/custom_theme/skin/theme_nested_skin/fixture_script.js",
            ),
            'localized skin file' => array(
                'fixture_script.js',
                array('_package' => 'package', '_theme' => 'custom_theme', '_skin' => 'theme_nested_skin'),
                "%s/frontend/package/custom_theme/skin/theme_nested_skin/locale/ru_RU/fixture_script.js",
                'ru_RU',
            ),
            'general modular skin file' => array(
                'fixture_script.js',
                array(
                    '_package' => 'package',
                    '_theme'   => 'custom_theme',
                    '_skin'    => 'theme_nested_skin',
                    '_module'  => 'Fixture_Module'
                ),
                "%s/frontend/package/custom_theme/skin/theme_nested_skin/Fixture_Module/fixture_script.js",
            ),
            'localized modular skin file' => array(
                'fixture_script.js',
                array(
                    '_package' => 'package',
                    '_theme'   => 'custom_theme',
                    '_skin'    => 'theme_nested_skin',
                    '_module'  => 'Fixture_Module'
                ),
                "%s/frontend/package/custom_theme/skin/theme_nested_skin/locale/ru_RU/Fixture_Module/fixture_script.js",
                'ru_RU',
            ),
        );
    }

    /**
     * Test for the skin files fallback to the JavaScript libraries
     *
     * @param string $skinFile
     * @param array $designParams
     * @param string|null $expectedFilename
     * @param string|null $locale
     *
     * @dataProvider skinFileJsLibFallbackDataProvider
     */
    public function testSkinFileJsLibFallback($skinFile, array $designParams, $expectedFilename, $locale = null)
    {
        $this->testSkinFileFallback($skinFile, $designParams, $expectedFilename, $locale);
    }

    public function skinFileJsLibFallbackDataProvider()
    {
        return array(
            'lib file in theme' => array(
                'mage/jquery-no-conflict.js',
                array('_package' => 'package', '_theme' => 'custom_theme', '_skin' => 'theme_nested_skin'),
                "%s/frontend/package/custom_theme/skin/theme_nested_skin/mage/jquery-no-conflict.js",
            ),
            'lib file in js lib' => array(
                'mage/jquery-no-conflict.js',
                array('_package' => 'package', '_theme' => 'custom_theme', '_skin' => 'default'),
                '%s/pub/js/mage/jquery-no-conflict.js',
            ),
        );
    }
}
