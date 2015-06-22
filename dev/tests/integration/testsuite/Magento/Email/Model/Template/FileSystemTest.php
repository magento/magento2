<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Email\Model\Template;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * FileSystemTest Test
 *
 * @package Magento\View
 * @magentoDataFixture Magento/Framework/View/_files/fallback/themes_registration.php
 */
class FileSystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     */
    private $themeFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->themeFactory = Bootstrap::getObjectManager()
            ->get('Magento\Framework\View\Design\Theme\FlyweightFactory');
    }

    /**
     * Test for the email template files fallback according to the themes inheritance
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Email/Model/_files/design/themes.php
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

        /** @var \Magento\Email\Model\Template\FileSystem $model */
        $model = Bootstrap::getObjectManager()
            ->create('Magento\Email\Model\Template\FileSystem');

        $themeModel = $this->themeFactory->create($themePath);

        $designParams = [
            'area' => $area,
            'themeModel' => $themeModel,
            'locale' => \Magento\Setup\Module\I18n\Locale::DEFAULT_SYSTEM_LOCALE,
        ];

        $actualFilename = $model->getEmailTemplateFileName($file, $module, $designParams);
        if ($expectedFilename) {
            $this->assertInternalType('string', $actualFilename);
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFalse($actualFilename);
        }
    }

    public function getEmailTemplateFileDataProvider()
    {
        return [
            'no fallback' => [
                'account_new.html',
                'Vendor/custom_theme',
                'Magento_Customer',
                '%s/frontend/Vendor/custom_theme/Magento_Customer/email/account_new.html',
            ],
            'inherit same package & parent theme' => [
                'account_new_confirmation.html',
                'Vendor/custom_theme',
                'Magento_Customer',
                '%s/frontend/Vendor/default/Magento_Customer/email/account_new_confirmation.html',
            ],
            'inherit parent package & grandparent theme' => [
                'account_new_confirmed.html',
                'Vendor/custom_theme',
                'Magento_Customer',
                '%s/frontend/Magento/default/Magento_Customer/email/account_new_confirmed.html',
            ],
        ];
    }
}
