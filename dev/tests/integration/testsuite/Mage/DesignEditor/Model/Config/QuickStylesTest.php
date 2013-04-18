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
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_Config_QuickStylesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_DesignEditor_Model_Config_Control_QuickStyles
     */
    protected $_model;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_design;

    /**
     * Initialize dependencies
     */
    protected function setUp()
    {
        $this->_design = Mage::getObjectManager()->get('Mage_Core_Model_Design_Package');
        $this->_design->setDesignTheme('package/test', Mage_Core_Model_Design_Package::DEFAULT_AREA);
        $quickStylesPath = $this->_design->getFilename('Mage_DesignEditor::controls/quick_styles.xml');
        $this->assertFileExists($quickStylesPath);
        $this->_model = Mage::getObjectManager()->create('Mage_DesignEditor_Model_Config_Control_QuickStyles',
            array('configFiles' => array($quickStylesPath)));
    }

    /**
     * Test control data
     *
     * @magentoDataFixture Mage/DesignEditor/Model/_files/design/themes.php
     * @dataProvider getTestDataProvider
     * @magentoAppIsolation enabled
     * @param string $controlName
     * @param array $expectedControlData
     */
    public function testLoadConfiguration($controlName, $expectedControlData)
    {
        $this->assertEquals($expectedControlData, $this->_model->getControlData($controlName));
    }

    /**
     * Data provider with sample data for test controls
     *
     * @return array
     */
    public function getTestDataProvider()
    {
        return array(
            array('headers', array(
                'type'         => 'logo',
                'layoutParams' => array('title' => 'Headers', 'column' => 'left'),
                'components'   => array (
                    'logo-picker'   => array (
                        'type'      => 'color-picker',
                        'selector'  => '.body .div',
                        'attribute' => 'background-color',
                        'var'       => 'Mage_DesignEditor::test_var_key1',
                    ),
                    'font-selector' => array (
                        'type'      => 'font-selector',
                        'selector'  => '*',
                        'attribute' => 'font-family',
                        'options'   => array('Arial, Verdana, Georgia', 'Tahoma'),
                        'var'       => 'Mage_DesignEditor::test_var_key2',
                    ),
                    'test-control' => array (
                        'type'       => 'test-control',
                        'components' => array (
                            'image-uploader' => array (
                                'type'      => 'logo-uploader',
                                'selector'  => '.test-logo-1',
                                'attribute' => 'background-image',
                                'var'       => 'Mage_DesignEditor::test_var_key3',
                            )
                        )
                    )
                )
            )),
            array('logo-uploader', array(
                'type'         => 'logo-uploader',
                'selector'     => '.test-logo-2',
                'attribute'    => 'background-image',
                'layoutParams' => array('title' => 'Logo Uploader', 'column' => 'center'),
                'var'          => 'Mage_DesignEditor::test_var_key4',
            )),
            array('background-color-picker', array(
                'type'         => 'color-picker',
                'layoutParams' => array('title' => 'Background Color', 'column' => 'right'),
                'selector'     => '.body .div',
                'attribute'    => 'background-color',
                'var'          => 'Mage_DesignEditor::test_var_key5',
            )),
        );
    }
}
