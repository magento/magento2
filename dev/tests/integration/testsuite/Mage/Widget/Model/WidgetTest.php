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
 * @package     Mage_Widget
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Widget_Model_WidgetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Widget_Model_Widget
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Widget_Model_Widget');
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testGetWidgetsArray()
    {
        $declaredWidgets = $this->_model->getWidgetsArray();
        $this->assertNotEmpty($declaredWidgets);
        $this->assertInternalType('array', $declaredWidgets);
        foreach ($declaredWidgets as $row) {
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('code', $row);
            $this->assertArrayHasKey('type', $row);
            $this->assertArrayHasKey('description', $row);
        }
    }

    /**
     * @param string $type
     * @param string $expectedFile
     * @return string
     *
     * @dataProvider getPlaceholderImageUrlDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetPlaceholderImageUrl($type, $expectedFile)
    {
        Mage::getDesign()->setDesignTheme('default/basic', 'adminhtml');
        $expectedPubFile = Mage::getBaseDir('media') . "/theme/static/adminhtml/default/basic/en_US/{$expectedFile}";
        if (file_exists($expectedPubFile)) {
            unlink($expectedPubFile);
        }
        $expectedPubFile = str_replace('/', DIRECTORY_SEPARATOR, $expectedPubFile);
        $url = $this->_model->getPlaceholderImageUrl($type);
        $this->assertStringEndsWith($expectedFile, $url);
        $this->assertFileExists($expectedPubFile);
        return $expectedPubFile;
    }

    /**
     * @return array
     */
    public function getPlaceholderImageUrlDataProvider()
    {
        return array(
            'custom image'  => array(
                'Mage_Catalog_Block_Product_Widget_New',
                'Mage_Catalog/images/product_widget_new.gif'
            ),
            'default image' => array(
                'non_existing_widget_type',
                'Mage_Widget/placeholder.gif'
            ),
        );
    }

    /**
     * Tests, that theme file is found anywhere in theme folders, not only in module directory.
     *
     * @magentoDataFixture Mage/Widget/_files/themes.php
     * @magentoAppIsolation enabled
     */
    public function testGetPlaceholderImageUrlAtTheme()
    {
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
            Mage_Core_Model_App::INIT_OPTION_DIRS => array(
                Mage_Core_Model_Dir::THEMES => dirname(__DIR__) . '/_files/design'
            )
        ));
        $actualFile = $this->testGetPlaceholderImageUrl(
            'Mage_Catalog_Block_Product_Widget_New',
            'Mage_Catalog/images/product_widget_new.gif'
        );

        $expectedFile = dirname(__DIR__)
            . '/_files/design/adminhtml/default/basic/Mage_Catalog/images/product_widget_new.gif';
        $this->assertFileEquals($expectedFile, $actualFile);
    }
}
