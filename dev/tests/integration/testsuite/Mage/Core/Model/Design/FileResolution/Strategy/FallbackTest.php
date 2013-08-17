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

class Mage_Core_Model_Design_FileResolution_Strategy_FallbackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_baseDir;

    /**
     * @var string
     */
    protected $_viewDir;

    public function setUp()
    {
        $this->_baseDir = realpath(__DIR__ . '/../../../_files/fallback');
        $this->_viewDir = $this->_baseDir . DIRECTORY_SEPARATOR . 'design';
    }

    /**
     * Build a model to test
     *
     * @return Mage_Core_Model_Design_FileResolution_Strategy_Fallback
     */
    protected function _buildModel()
    {
        // Prepare config with directories
        $dirs = new Mage_Core_Model_Dir(
            $this->_baseDir,
            array(),
            array(Mage_Core_Model_Dir::THEMES => $this->_viewDir)
        );

        return Mage::getObjectManager()->create(
            'Mage_Core_Model_Design_FileResolution_Strategy_Fallback',
            array('fallbackFactory' => new Mage_Core_Model_Design_Fallback_Factory($dirs))
        );
    }

    /**
     * Compose custom theme model with designated path
     *
     * @param string $area
     * @param string $themePath
     * @return Mage_Core_Model_Theme
     */
    protected function _getThemeModel($area, $themePath)
    {
        /** @var $collection Mage_Core_Model_Theme_Collection */
        $collection = Mage::getModel('Mage_Core_Model_Theme_Collection');
        $themeModel = $collection->setBaseDir($this->_viewDir)
            ->addDefaultPattern()
            ->addFilter('theme_path', $themePath)
            ->addFilter('area', $area)
            ->getFirstItem();
        return $themeModel;
    }

    /**
     * @param string $file
     * @param string $area
     * @param string $themePath
     * @param string|null $module
     * @param string|null $expectedFilename
     *
     * @dataProvider getFileDataProvider
     */
    public function testGetFile($file, $area, $themePath, $module, $expectedFilename)
    {
        $model = $this->_buildModel($area, $themePath, null);
        $themeModel = $this->_getThemeModel($area, $themePath);

        $expectedFilename = str_replace('/', DS, $expectedFilename);
        $actualFilename = $model->getFile($area, $themeModel, $file, $module);
        if ($expectedFilename) {
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFileNotExists($actualFilename);
        }
    }

    public function getFileDataProvider()
    {
        return array(
            'non-modular: no default inheritance' => array(
                'fixture_template.phtml', 'frontend', 'package/standalone_theme', null,
                null,
            ),
            'non-modular: inherit same package & parent theme' => array(
                'fixture_template.phtml', 'frontend', 'package/custom_theme', null,
                '%s/frontend/package/default/fixture_template.phtml',
            ),
            'non-modular: inherit same package & grandparent theme' => array(
                'fixture_template.phtml', 'frontend', 'package/custom_theme2', null,
                '%s/frontend/package/default/fixture_template.phtml',
            ),
            'non-modular: inherit parent package & parent theme' => array(
                'fixture_template_two.phtml', 'frontend', 'test/external_package_descendant', null,
                '%s/frontend/package/custom_theme/fixture_template_two.phtml',
            ),
            'non-modular: inherit parent package & grandparent theme' => array(
                'fixture_template.phtml', 'frontend', 'test/external_package_descendant', null,
                '%s/frontend/package/default/fixture_template.phtml',
            ),
            'modular: no default inheritance' => array(
                'fixture_template.phtml', 'frontend', 'package/standalone_theme', 'Fixture_Module',
                null,
            ),
            'modular: no fallback to non-modular file' => array(
                'fixture_template.phtml', 'frontend', 'package/default', 'NonExisting_Module',
                null,
            ),
            'modular: inherit same package & parent theme' => array(
                'fixture_template.phtml', 'frontend', 'package/custom_theme', 'Fixture_Module',
                '%s/frontend/package/default/Fixture_Module/fixture_template.phtml',
            ),
            'modular: inherit same package & grandparent theme' => array(
                'fixture_template.phtml', 'frontend', 'package/custom_theme2', 'Fixture_Module',
                '%s/frontend/package/default/Fixture_Module/fixture_template.phtml',
            ),
            'modular: inherit parent package & parent theme' => array(
                'fixture_template_two.phtml', 'frontend', 'test/external_package_descendant', 'Fixture_Module',
                '%s/frontend/package/custom_theme/Fixture_Module/fixture_template_two.phtml',
            ),
            'modular: inherit parent package & grandparent theme' => array(
                'fixture_template.phtml', 'frontend', 'test/external_package_descendant', 'Fixture_Module',
                '%s/frontend/package/default/Fixture_Module/fixture_template.phtml',
            ),
        );
    }

    /**
     * @param string $file
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string|null $expectedFilename
     *
     * @dataProvider getLocaleFileDataProvider
     */
    public function testGetLocaleFile($file, $area, $themePath, $locale, $expectedFilename)
    {
        $model = $this->_buildModel($area, $themePath, $locale);
        $themeModel = $this->_getThemeModel($area, $themePath);

        $expectedFilename = str_replace('/', DIRECTORY_SEPARATOR, $expectedFilename);
        $actualFilename = $model->getLocaleFile($area, $themeModel, $locale, $file);
        if ($expectedFilename) {
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFileNotExists($actualFilename);
        }
    }

    public function getLocaleFileDataProvider()
    {
        return array(
            'no default inheritance' => array(
                'fixture_translate.csv', 'frontend', 'package/standalone_theme', 'en_US',
                null,
            ),
            'inherit same package & parent theme' => array(
                'fixture_translate.csv', 'frontend', 'package/custom_theme', 'en_US',
                '%s/frontend/package/default/locale/en_US/fixture_translate.csv',
            ),
            'inherit same package & grandparent theme' => array(
                'fixture_translate.csv', 'frontend', 'package/custom_theme2', 'en_US',
                '%s/frontend/package/default/locale/en_US/fixture_translate.csv',
            ),
            'inherit parent package & parent theme' => array(
                'fixture_translate_two.csv', 'frontend', 'test/external_package_descendant', 'en_US',
                '%s/frontend/package/custom_theme/locale/en_US/fixture_translate_two.csv',
            ),
            'inherit parent package & grandparent theme' => array(
                'fixture_translate.csv', 'frontend', 'test/external_package_descendant', 'en_US',
                '%s/frontend/package/default/locale/en_US/fixture_translate.csv',
            ),
        );
    }

    /**
     * Test for the skin files fallback according to the themes inheritance
     *
     * @param string $file
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param string|null $expectedFilename
     *
     * @dataProvider getViewFileDataProvider
     */
    public function testGetViewFile($file, $area, $themePath, $locale, $module, $expectedFilename)
    {
        $model = $this->_buildModel($area, $themePath, $locale);
        $themeModel = $this->_getThemeModel($area, $themePath);

        $expectedFilename = str_replace('/', DIRECTORY_SEPARATOR, $expectedFilename);
        $actualFilename = $model->getViewFile($area, $themeModel, $locale, $file, $module);
        if ($expectedFilename) {
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFileNotExists($actualFilename);
        }
    }

    public function getViewFileDataProvider()
    {
        return array(
            'non-modular: no default inheritance' => array(
                'fixture_script.js', 'frontend', 'package/standalone_theme', null, null,
                null,
            ),
            'non-modular: inherit same package & parent theme' => array(
                'fixture_script.js', 'frontend', 'package/custom_theme', null, null,
                '%s/frontend/package/default/fixture_script.js',
            ),
            'non-modular: inherit same package & grandparent theme' => array(
                'fixture_script.js', 'frontend', 'package/custom_theme2', null, null,
                '%s/frontend/package/default/fixture_script.js',
            ),
            'non-modular: inherit parent package & parent theme' => array(
                'fixture_script_two.js', 'frontend', 'test/external_package_descendant', null, null,
                '%s/frontend/package/custom_theme/fixture_script_two.js',
            ),
            'non-modular: inherit parent package & grandparent theme' => array(
                'fixture_script.js', 'frontend', 'test/external_package_descendant', null, null,
                '%s/frontend/package/default/fixture_script.js',
            ),
            'non-modular: fallback to non-localized file' => array(
                'fixture_script.js', 'frontend', 'package/default', 'en_US', null,
                '%s/frontend/package/default/fixture_script.js',
            ),
            'non-modular: localized file' => array(
                'fixture_script.js', 'frontend', 'package/default', 'ru_RU', null,
                '%s/frontend/package/default/locale/ru_RU/fixture_script.js',
            ),
            'non-modular: override js lib file' => array(
                'mage/script.js', 'frontend', 'package/custom_theme', null, null,
                '%s/frontend/package/custom_theme/mage/script.js',
            ),
            'non-modular: inherit js lib file' => array(
                'mage/script.js', 'frontend', 'package/default', null, null,
                '%s/pub/lib/mage/script.js',
            ),
            'modular: no default inheritance' => array(
                'fixture_script.js', 'frontend', 'package/standalone_theme', null, 'Fixture_Module',
                null,
            ),
            'modular: no fallback to non-modular file' => array(
                'fixture_script.js', 'frontend', 'package/default', null, 'NonExisting_Module',
                null,
            ),
            'modular: no fallback to js lib file' => array(
                'mage/script.js', 'frontend', 'package/default', null, 'Fixture_Module',
                null,
            ),
            'modular: no fallback to non-modular localized file' => array(
                'fixture_script.js', 'frontend', 'package/default', 'ru_RU', 'NonExisting_Module',
                null,
            ),
            'modular: inherit same package & parent theme' => array(
                'fixture_script.js', 'frontend', 'package/custom_theme', null, 'Fixture_Module',
                '%s/frontend/package/default/Fixture_Module/fixture_script.js',
            ),
            'modular: inherit same package & grandparent theme' => array(
                'fixture_script.js', 'frontend', 'package/custom_theme2', null, 'Fixture_Module',
                '%s/frontend/package/default/Fixture_Module/fixture_script.js',
            ),
            'modular: inherit parent package & parent theme' => array(
                'fixture_script_two.js', 'frontend', 'test/external_package_descendant', null, 'Fixture_Module',
                '%s/frontend/package/custom_theme/Fixture_Module/fixture_script_two.js',
            ),
            'modular: inherit parent package & grandparent theme' => array(
                'fixture_script.js', 'frontend', 'test/external_package_descendant', null, 'Fixture_Module',
                '%s/frontend/package/default/Fixture_Module/fixture_script.js',
            ),
            'modular: fallback to non-localized file' => array(
                'fixture_script.js', 'frontend', 'package/default', 'en_US', 'Fixture_Module',
                '%s/frontend/package/default/Fixture_Module/fixture_script.js',
            ),
            'modular: localized file' => array(
                'fixture_script.js', 'frontend', 'package/custom_theme2', 'ru_RU', 'Fixture_Module',
                '%s/frontend/package/default/locale/ru_RU/Fixture_Module/fixture_script.js',
            ),
        );
    }
}
