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
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_MainTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main
     */
    protected $_block = null;

    protected function setUp()
    {
        $this->_block = Mage::app()->getLayout()
            ->createBlock('Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main');
    }

    protected function tearDown()
    {
        $this->_block = null;
        Mage::unregister('entity_attribute');
        Mage::unregister('attribute_type_hidden_fields');
        Mage::unregister('attribute_type_disabled_types');
    }

    public function testPrepareFormSystemAttribute()
    {
        Mage::register('entity_attribute', new Varien_Object(
                array('entity_type' => new Varien_Object(), 'id' => 1, 'is_user_defined' => false))
        );
        $this->_block->toHtml();
        $this->assertTrue(
            $this->_block->getForm()->getElement('base_fieldset')->getContainer()->getElement('apply_to')->getDisabled()
        );
    }

    public function testPrepareFormUserDefinedAttribute()
    {
        Mage::register('entity_attribute', new Varien_Object(
                array('entity_type' => new Varien_Object(), 'id' => 1, 'is_user_defined' => true))
        );
        $this->_block->toHtml();
        $this->assertFalse(
            $this->_block->getForm()->getElement('base_fieldset')->getContainer()->getElement('apply_to')->getDisabled()
        );
    }
}
