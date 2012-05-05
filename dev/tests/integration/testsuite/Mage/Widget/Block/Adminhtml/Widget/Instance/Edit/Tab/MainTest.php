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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_MainTest extends PHPUnit_Framework_TestCase
{
    public function testPackageThemeElement()
    {
        Mage::register('current_widget_instance', new Varien_Object());
        $block = Mage::app()->getLayout()->createBlock('Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Main');
        $block->toHtml();
        $element = $block->getForm()->getElement('package_theme');
        $this->assertInstanceOf('Varien_Data_Form_Element_Text', $element);
        $this->assertTrue($element->getDisabled());
    }
}
