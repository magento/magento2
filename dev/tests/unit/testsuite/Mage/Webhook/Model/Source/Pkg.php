<?php
/**
 * Parent class for Source tests that provides common functionality.
 *
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
 * @category    Mage
 * @package     Mage_Webhook
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Source_Pkg extends PHPUnit_Framework_TestCase
{
    /** Config values */
    const CONFIG_LABEL = 'blah';
    const CONFIG_STATUS = 'enabled';
    const TRANSLATED = '_translated';

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockTranslate;
    
    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockConfig;
    
    /** @var Mage_Core_Model_Config_Element */
    protected $_modelConfigElement;
    
    public function setUp()
    {
        $label = self::CONFIG_LABEL;
        $status = self::CONFIG_STATUS;
        $this->_modelConfigElement = new Mage_Core_Model_Config_Element(
            "<types><type><status>{$status}</status><label>{$label}</label></type></types>"
        );
        $this->_mockConfig = $this->getMockBuilder('Mage_Core_Model_Config')
            ->disableOriginalConstructor()->getMock();
        $this->_mockConfig->expects($this->any())
            ->method('getNode')
            ->will($this->returnValue($this->_modelConfigElement));
        $this->_mockTranslate = $this->getMockBuilder('Mage_Core_Model_Translate')
            ->disableOriginalConstructor()->getMock();
        $this->_mockTranslate->expects($this->any())
            ->method('translate')
            ->will($this->returnCallback(
                function ($args) {
                    return array_shift($args) . Mage_Webhook_Model_Source_Pkg::TRANSLATED;
                }
            ));
    }

    /**
     * Asserts that the elements array contains the expected label and value.
     *
     * @param $elements
     */
    protected function _assertElements($elements)
    {
        $this->assertSame(self::CONFIG_LABEL . self::TRANSLATED, $elements[0]['label']);
        $this->assertSame('type', $elements[0]['value']);
    }
}