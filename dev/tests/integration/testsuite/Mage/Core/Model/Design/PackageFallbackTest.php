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

/**
 * Tests for the view layer fallback mechanism
 * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
 */
class Mage_Core_Model_Design_PackageFallbackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_model = null;

    protected function setUp()
    {
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
            Mage::PARAM_APP_DIRS => array(
                Mage_Core_Model_Dir::THEMES => dirname(__DIR__) . '/_files/design'
            )
        ));
        $this->_model = Mage::getObjectManager()->create('Mage_Core_Model_Design_Package');
        $this->_model->setDesignTheme('test/default');
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testGetFilename()
    {
        $expected = '%s/frontend/test/default/Mage_Catalog/theme_template.phtml';
        $actual = $this->_model->getFilename('Mage_Catalog::theme_template.phtml', array());
        $this->_testExpectedVersusActualFilename($expected, $actual);
    }

    public function testGetLocaleFileName()
    {
        $expected = '%s/frontend/test/default/locale/en_US/translate.csv';
        $actual = $this->_model->getLocaleFileName('translate.csv', array());
        $this->_testExpectedVersusActualFilename($expected, $actual);
    }

    public function testGetViewFile()
    {
        $expected = '%s/frontend/package/custom_theme/Fixture_Module/fixture_script.js';
        $params = array(
            'package' => 'package',
            'theme' => 'custom_theme'
        );
        $actual = $this->_model->getViewFile('Fixture_Module::fixture_script.js', $params);
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
        $expected = str_replace('/', DIRECTORY_SEPARATOR, $expected);
        $this->assertStringMatchesFormat($expected, $actual);
        $this->assertFileExists($actual);
    }
}
