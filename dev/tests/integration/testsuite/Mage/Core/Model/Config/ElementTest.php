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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Config_ElementTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Element
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Config_Element(<<<XML
<?xml version="1.0"?>
<root>
    <is_test>
        <value_key>value</value_key>
        <value_sensitive_key>vaLue</value_sensitive_key>
        <false_key>false</false_key>
        <off_key>off</off_key>
        <regular_cdata><![CDATA[value]]></regular_cdata>
        <empty_cdata><![CDATA[]]></empty_cdata>
        <empty_text></empty_text>
    </is_test>
    <class_test>
        <class>Mage_Catalog_Model_Observer</class>
    </class_test>
    <model_test>
        <model>Mage_Catalog_Model_Observer</model>
    </model_test>
    <no_classname_test>
        <none/>
    </no_classname_test>
</root>
XML
        );
    }

    public function testIs()
    {
        $element = $this->_model->is_test;
        $this->assertTrue($element->is('value_key', 'value'));
        $this->assertTrue($element->is('value_sensitive_key', 'value'));
        $this->assertTrue($element->is('regular_cdata', 'value'));
        $this->assertFalse($element->is('false_key'));
        $this->assertFalse($element->is('empty_cdata'));
        $this->assertFalse($element->is('empty_text'));
    }

    public function testGetClassName()
    {
        $this->assertEquals('Mage_Catalog_Model_Observer', $this->_model->class_test->getClassName());
        $this->assertEquals('Mage_Catalog_Model_Observer', $this->_model->model_test->getClassName());
        $this->assertFalse($this->_model->no_classname_test->getClassName());
    }
}
