<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Tests for the view layer fallback mechanism
 * @magentoDataFixture Magento/Theme/Model/_files/design/themes.php
 */
class FileSystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_model = null;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\FileSystem'
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
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

    public function testGetViewFile()
    {
        $componentRegistrar = new ComponentRegistrar();
        if ($componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Fixture_Module') === null) {
            ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Fixture_Module', __DIR__);
        }
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
