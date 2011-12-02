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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Convert profile edit tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_Edit extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_edit');

        $model = Mage::registry('current_convert_profile');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('General Information'),
            'class'=>'fieldset-wide'
        ));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Profile Name'),
            'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Profile Name'),
            'required' => true,
        ));

        $fieldset->addField('actions_xml', 'textarea', array(
            'name' => 'actions_xml',
            'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Actions XML'),
            'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Actions XML'),
            'style' => 'height:30em',
            'required' => true,
        ));


        $form->setValues($model->getData());

        $this->setForm($form);

        return $this;
    }
}
