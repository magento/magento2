<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution;

use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Fallback Test
 *
 * @package Magento\View
 * @magentoDataFixture Magento/Framework/View/_files/fallback/themes_registration.php
 */
class FallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     */
    private $themeFactory;

    protected function setUp()
    {
        Bootstrap::getInstance()->reinitialize([
            AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [
                DirectoryList::THEMES => [
                    'path' => __DIR__ . '/../../_files/fallback/design',
                ],
                DirectoryList::LIB_WEB => [
                    'path' => __DIR__ . '/../../_files/fallback/lib/web',
                ],
            ],
        ]);
        /** @var \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory */
        $this->themeFactory = Bootstrap::getObjectManager()
            ->get('Magento\Framework\View\Design\Theme\FlyweightFactory');
    }

    /**
     * @param string $file
     * @param string $themePath
     * @param string|null $module
     * @param string|null $expectedFilename
     *
     * @dataProvider getTemplateFileDataProvider
     */
    public function testGetTemplateFile($file, $themePath, $module, $expectedFilename)
    {
        /** @var \Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile $model */
        $model = Bootstrap::getObjectManager()
            ->create('Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile');
        $themeModel = $this->themeFactory->create($themePath);

        $actualFilename = $model->getFile('frontend', $themeModel, $file, $module);
        if ($expectedFilename) {
            $this->assertInternalType('string', $actualFilename);
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFalse($actualFilename);
        }
    }

    /**
     * @return array
     */
    public function getTemplateFileDataProvider()
    {
        return [
            'non-modular: no default inheritance' => [
                'fixture_template.phtml', 'Vendor/standalone_theme', null,
                null,
            ],
            'non-modular: inherit parent theme' => [
                'fixture_template.phtml', 'Vendor/custom_theme', null,
                '%s/frontend/Vendor/default/templates/fixture_template.phtml',
            ],
            'non-modular: inherit grandparent theme' => [
                'fixture_template.phtml', 'Vendor/custom_theme2', null,
                '%s/frontend/Vendor/default/templates/fixture_template.phtml',
            ],
            'modular: no default inheritance' => [
                'fixture_template.phtml', 'Vendor/standalone_theme', 'Fixture_Module',
                null,
            ],
            'modular: no fallback to non-modular file' => [
                'fixture_template.phtml', 'Vendor/default', 'NonExisting_Module',
                null,
            ],
            'modular: inherit parent theme' => [
                'fixture_template.phtml', 'Vendor/custom_theme', 'Fixture_Module',
                '%s/frontend/Vendor/default/Fixture_Module/templates/fixture_template.phtml',
            ],
            'modular: inherit grandparent theme' => [
                'fixture_template.phtml', 'Vendor/custom_theme2', 'Fixture_Module',
                '%s/frontend/Vendor/default/Fixture_Module/templates/fixture_template.phtml',
            ],
        ];
    }

    /**
     * @param string $themePath
     * @param string $locale
     * @param string|null $expectedFilename
     *
     * @dataProvider getLocaleFileDataProvider
     */
    public function testGetI18nCsvFile($themePath, $locale, $expectedFilename)
    {
        /** @var \Magento\Framework\View\Design\FileResolution\Fallback\File $model */
        $model = Bootstrap::getObjectManager()->create('Magento\Framework\View\Design\FileResolution\Fallback\File');
        $themeModel = $this->themeFactory->create($themePath);

        $actualFilename = $model->getFile('frontend', $themeModel, 'i18n/' . $locale . '.csv');

        if ($expectedFilename) {
            $this->assertInternalType('string', $actualFilename);
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFalse($actualFilename);
        }
    }

    public function getLocaleFileDataProvider()
    {
        return [
            'no default inheritance' => [
                'Vendor/standalone_theme', 'en_US',
                null,
            ],
            'inherit parent theme' => [
                'Vendor/custom_theme', 'en_US',
                '%s/frontend/Vendor/custom_theme/i18n/en_US.csv',
            ],
            'inherit grandparent theme' => [
                'Vendor/custom_theme2', 'en_US',
                '%s/frontend/Vendor/custom_theme/i18n/en_US.csv',
            ],
        ];
    }

    /**
     * Test for the static files fallback according to the themes inheritance
     *
     * @param string $file
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param string|null $expectedFilename
     *
     * @dataProvider getViewFileDataProvider
     */
    public function testGetViewFile($file, $themePath, $locale, $module, $expectedFilename)
    {
        /** @var \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile $model */
        $model = Bootstrap::getObjectManager()
            ->create('Magento\Framework\View\Design\FileResolution\Fallback\StaticFile');
        $themeModel = $this->themeFactory->create($themePath);

        $actualFilename = $model->getFile('frontend', $themeModel, $locale, $file, $module);
        if ($expectedFilename) {
            $this->assertInternalType('string', $actualFilename);
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFalse($actualFilename);
        }
    }

    public function getViewFileDataProvider()
    {
        return [
            'non-modular: no default inheritance' => [
                'fixture_script.js', 'Vendor/standalone_theme', null, null,
                null,
            ],
            'non-modular: inherit same package & parent theme' => [
                'fixture_script.js', 'Vendor/custom_theme', null, null,
                '%s/frontend/Vendor/default/web/fixture_script.js',
            ],
            'non-modular: inherit same package & grandparent theme' => [
                'fixture_script.js', 'Vendor/custom_theme2', null, null,
                '%s/frontend/Vendor/default/web/fixture_script.js',
            ],
            'non-modular: fallback to non-localized file' => [
                'fixture_script.js', 'Vendor/default', 'en_US', null,
                '%s/frontend/Vendor/default/web/fixture_script.js',
            ],
            'non-modular: localized file' => [
                'fixture_script.js', 'Vendor/default', 'ru_RU', null,
                '%s/frontend/Vendor/default/web/i18n/ru_RU/fixture_script.js',
            ],
            'non-modular: override js lib file' => [
                'mage/script.js', 'Vendor/custom_theme', null, null,
                '%s/frontend/Vendor/custom_theme/web/mage/script.js',
            ],
            'non-modular: inherit js lib file' => [
                'mage/script.js', 'Vendor/default', null, null,
                '%s/lib/web/mage/script.js',
            ],
            'modular: no default inheritance' => [
                'fixture_script.js', 'Vendor/standalone_theme', null, 'Fixture_Module',
                null,
            ],
            'modular: no fallback to non-modular file' => [
                'fixture_script.js', 'Vendor/default', null, 'NonExisting_Module',
                null,
            ],
            'modular: no fallback to js lib file' => [
                'mage/script.js', 'Vendor/default', null, 'Fixture_Module',
                null,
            ],
            'modular: no fallback to non-modular localized file' => [
                'fixture_script.js', 'Vendor/default', 'ru_RU', 'NonExisting_Module',
                null,
            ],
            'modular: inherit same package & parent theme' => [
                'fixture_script.js', 'Vendor/custom_theme', null, 'Fixture_Module',
                '%s/frontend/Vendor/default/Fixture_Module/web/fixture_script.js',
            ],
            'modular: inherit same package & grandparent theme' => [
                'fixture_script.js', 'Vendor/custom_theme2', null, 'Fixture_Module',
                '%s/frontend/Vendor/default/Fixture_Module/web/fixture_script.js',
            ],
            'modular: fallback to non-localized file' => [
                'fixture_script.js', 'Vendor/default', 'en_US', 'Fixture_Module',
                '%s/frontend/Vendor/default/Fixture_Module/web/fixture_script.js',
            ],
            'modular: localized file' => [
                'fixture_script.js', 'Vendor/custom_theme2', 'ru_RU', 'Fixture_Module',
                '%s/frontend/Vendor/default/Fixture_Module/web/i18n/ru_RU/fixture_script.js',
            ],
        ];
    }
}
