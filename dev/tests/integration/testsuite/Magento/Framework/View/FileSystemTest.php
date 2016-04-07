<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use \Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests for the view layer fallback mechanism
 * @magentoComponentsDir Magento/Theme/Model/_files/design
 * @magentoDbIsolation enabled
 */
class FileSystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_model = null;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $objectManager->get(
            'Magento\Theme\Model\Theme\Registration'
        );
        $registration->register();
        $objectManager->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $this->_model = $objectManager->create(
            'Magento\Framework\View\FileSystem'
        );
        $objectManager->get(
            'Magento\Framework\View\DesignInterface'
        )->setDesignTheme(
            'Test_FrameworkThemeTest/default'
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

    /**
     * @magentoComponentsDir Magento/Framework/View/_files/Fixture_Module
     */
    public function testGetViewFile()
    {
        $expected = '%s/frontend/Vendor/custom_theme/Fixture_Module/web/fixture_script.js';
        $params = ['theme' => 'Vendor_FrameworkThemeTest/custom_theme'];
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
