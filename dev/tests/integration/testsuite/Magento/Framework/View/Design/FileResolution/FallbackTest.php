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
 * @magentoComponentsDir Magento/Framework/View/_files/fallback
 * @magentoDbIsolation enabled
 */
class FallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     */
    private $themeFactory;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $objectManager->get(
            'Magento\Theme\Model\Theme\Registration'
        );
        $registration->register();
        /** @var \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory */
        $this->themeFactory = $objectManager
            ->get('Magento\Framework\View\Design\Theme\FlyweightFactory');
    }

    /**
     * Reinitialize environment with test directories
     *
     * Since the testGetEmailTemplateFile test uses a @magentoDataFixture that reinitializes the environment, we
     * must reinitialize the environment only when a test specifically requests it
     *
     * @return void
     */
    protected function reinitializeEnvironment()
    {
        Bootstrap::getInstance()->reinitialize([
            AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [
                DirectoryList::LIB_WEB => [
                    'path' => __DIR__ . '/../../_files/fallback/lib/web',
                ],
            ],
        ]);
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
        $this->reinitializeEnvironment();
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
                'fixture_template.phtml', 'Vendor_ViewTest/standalone_theme', null,
                null,
            ],
            'non-modular: inherit parent theme' => [
                'fixture_template.phtml', 'Vendor_ViewTest/custom_theme', null,
                '%s/frontend/Vendor/default/templates/fixture_template.phtml',
            ],
            'non-modular: inherit grandparent theme' => [
                'fixture_template.phtml', 'Vendor_ViewTest/custom_theme2', null,
                '%s/frontend/Vendor/default/templates/fixture_template.phtml',
            ],
            'modular: no default inheritance' => [
                'fixture_template.phtml', 'Vendor_ViewTest/standalone_theme', 'ViewTest_Module',
                null,
            ],
            'modular: no fallback to non-modular file' => [
                'nonexistent_fixture_script.phtml', 'Vendor_ViewTest/default', 'ViewTest_Module',
                null,
            ],
            'modular: inherit parent theme' => [
                'fixture_template.phtml', 'Vendor_ViewTest/custom_theme', 'ViewTest_Module',
                '%s/frontend/Vendor/default/ViewTest_Module/templates/fixture_template.phtml',
            ],
            'modular: inherit grandparent theme' => [
                'fixture_template.phtml', 'Vendor_ViewTest/custom_theme2', 'ViewTest_Module',
                '%s/frontend/Vendor/default/ViewTest_Module/templates/fixture_template.phtml',
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
        $this->reinitializeEnvironment();
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
                'Vendor_ViewTest/standalone_theme', 'en_US',
                null,
            ],
            'inherit parent theme' => [
                'Vendor_ViewTest/custom_theme', 'en_US',
                '%s/frontend/Vendor/custom_theme/i18n/en_US.csv',
            ],
            'inherit grandparent theme' => [
                'Vendor_ViewTest/custom_theme2', 'en_US',
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
        $this->reinitializeEnvironment();
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
                'fixture_script.js', 'Vendor_ViewTest/standalone_theme', null, null,
                null,
            ],
            'non-modular: inherit same package & parent theme' => [
                'fixture_script.js', 'Vendor_ViewTest/custom_theme', null, null,
                '%s/frontend/Vendor/default/web/fixture_script.js',
            ],
            'non-modular: inherit same package & grandparent theme' => [
                'fixture_script.js', 'Vendor_ViewTest/custom_theme2', null, null,
                '%s/frontend/Vendor/default/web/fixture_script.js',
            ],
            'non-modular: fallback to non-localized file' => [
                'fixture_script.js', 'Vendor_ViewTest/default', 'en_US', null,
                '%s/frontend/Vendor/default/web/fixture_script.js',
            ],
            'non-modular: localized file' => [
                'fixture_script.js', 'Vendor_ViewTest/default', 'ru_RU', null,
                '%s/frontend/Vendor/default/web/i18n/ru_RU/fixture_script.js',
            ],
            'non-modular: override js lib file' => [
                'mage/script.js', 'Vendor_ViewTest/custom_theme', null, null,
                '%s/frontend/Vendor/custom_theme/web/mage/script.js',
            ],
            'non-modular: inherit js lib file' => [
                'mage/script.js', 'Vendor_ViewTest/default', null, null,
                '%s/lib/web/mage/script.js',
            ],
            'modular: no default inheritance' => [
                'fixture_script.js', 'Vendor_ViewTest/standalone_theme', null, 'ViewTest_Module',
                null,
            ],
            'modular: no fallback to non-modular file' => [
                'nonexistent_fixture_script.js', 'Vendor_ViewTest/default', null, 'ViewTest_Module',
                null,
            ],
            'modular: no fallback to js lib file' => [
                'mage/script.js', 'Vendor_ViewTest/default', null, 'ViewTest_Module',
                null,
            ],
            'modular: no fallback to non-modular localized file' => [
                'nonexistent_fixture_script.js', 'Vendor_ViewTest/default', 'ru_RU', 'ViewTest_Module',
                null,
            ],
            'modular: inherit same package & parent theme' => [
                'fixture_script.js', 'Vendor_ViewTest/custom_theme', null, 'ViewTest_Module',
                '%s/frontend/Vendor/default/ViewTest_Module/web/fixture_script.js',
            ],
            'modular: inherit same package & grandparent theme' => [
                'fixture_script.js', 'Vendor_ViewTest/custom_theme2', null, 'ViewTest_Module',
                '%s/frontend/Vendor/default/ViewTest_Module/web/fixture_script.js',
            ],
            'modular: fallback to non-localized file' => [
                'fixture_script.js', 'Vendor_ViewTest/default', 'en_US', 'ViewTest_Module',
                '%s/frontend/Vendor/default/ViewTest_Module/web/fixture_script.js',
            ],
            'modular: localized file' => [
                'fixture_script.js', 'Vendor_ViewTest/custom_theme2', 'ru_RU', 'ViewTest_Module',
                '%s/frontend/Vendor/default/ViewTest_Module/web/i18n/ru_RU/fixture_script.js',
            ],
        ];
    }

    /**
     * Test for the email template files fallback according to the themes inheritance
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoComponentsDir Magento/Email/Model/_files/design
     *
     * @param string $file
     * @param string $themePath
     * @param string $module
     * @param string|null $expectedFilename
     *
     * @dataProvider getEmailTemplateFileDataProvider
     */
    public function testGetEmailTemplateFile($file, $themePath, $module, $expectedFilename)
    {
        $area = \Magento\Framework\App\Area::AREA_FRONTEND;

        /** @var \Magento\Framework\View\Design\FileResolution\Fallback\EmailTemplateFile $model */
        $model = Bootstrap::getObjectManager()
            ->create('Magento\Framework\View\Design\FileResolution\Fallback\EmailTemplateFile');

        $themeModel = $this->themeFactory->create($themePath);
        $locale = \Magento\Setup\Module\I18n\Locale::DEFAULT_SYSTEM_LOCALE;

        $actualFilename = $model->getFile($area, $themeModel, $locale, $file, $module);
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
    public function getEmailTemplateFileDataProvider()
    {
        return [
            'no fallback' => [
                'account_new.html',
                'Vendor_EmailTest/custom_theme',
                'Magento_Customer',
                '%s/frontend/Vendor/custom_theme/Magento_Customer/email/account_new.html',
            ],
            'inherit same package & parent theme' => [
                'account_new_confirmation.html',
                'Vendor_EmailTest/custom_theme',
                'Magento_Customer',
                '%s/frontend/Vendor/default/Magento_Customer/email/account_new_confirmation.html',
            ],
            'inherit parent package & grandparent theme' => [
                'account_new_confirmed.html',
                'Vendor_EmailTest/custom_theme',
                'Magento_Customer',
                '%s/frontend/Magento/default/Magento_Customer/email/account_new_confirmed.html',
            ],
        ];
    }
}
