<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Tests for the view layer fallback mechanism
 * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
 */
class FileSystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_model = null;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(
            [
                Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [
                    DirectoryList::THEMES => [
                        'path' => dirname(dirname(__DIR__)) . '/Core/Model/_files/design',
                    ],
                ],
            ]
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\FileSystem'
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDesignTheme(
            'Test/default'
        );
    }

    public function testGetTemplateFileName()
    {
        $expected = '%s/frontend/Test/default/Magento_Catalog/templates/theme_template.phtml';
        $actual = $this->_model->getTemplateFileName('Magento_Catalog::theme_template.phtml', []);
        $this->_testExpectedVersusActualFilename($expected, $actual);
    }

    public function testGetFileNameAccordingToLocale()
    {
        $expected = '%s/frontend/Test/default/web/i18n/fr_FR/logo.gif';
        $actual = $this->_model->getStaticFileName('logo.gif', ['locale' => 'fr_FR']);
        $this->_testExpectedVersusActualFilename($expected, $actual);
    }

    public function testGetViewFile()
    {
        $expected = '%s/frontend/Vendor/custom_theme/Fixture_Module/web/fixture_script.js';
        $params = ['theme' => 'Vendor/custom_theme'];
        $actual = $this->_model->getStaticFileName('Fixture_Module::fixture_script.js', $params);
        $this->_testExpectedVersusActualFilename($expected, $actual);
    }

    /**
     * Tests expected vs actual found fallback filename
     *
     * @param string $expected
     * @param string $actual
     */
    protected function _testExpectedVersusActualFilename($expected, $actual)
    {
        $this->assertStringMatchesFormat($expected, $actual);
        $this->assertFileExists($actual);
    }
}
