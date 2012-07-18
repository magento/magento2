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
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Export edit form block
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Block_Adminhtml_Export_Edit_Form extends Mage_Backend_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML.
     *
     * @return Mage_ImportExport_Block_Adminhtml_Export_Edit_Form
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('Mage_ImportExport_Helper_Data');
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/getFilter'),
            'method' => 'post'
        ));
        $fieldsets = array();
        $fieldsets['base'] = $form->addFieldset('base_fieldset', array('legend' => $helper->__('Export Settings')));
        $fieldsets['base']->addField('entity', 'select', array(
            'name'     => 'entity',
            'title'    => $helper->__('Entity Type'),
            'label'    => $helper->__('Entity Type'),
            'required' => false,
            'onchange' => 'editForm.handleEntityTypeSelector();',
            'values'   => Mage::getModel('Mage_ImportExport_Model_Source_Export_Entity')->toOptionArray()
        ));
        $fieldsets['base']->addField('file_format', 'select', array(
            'name'     => 'file_format',
            'title'    => $helper->__('Export File Format'),
            'label'    => $helper->__('Export File Format'),
            'required' => false,
            'values'   => Mage::getModel('Mage_ImportExport_Model_Source_Export_Format_File')->toOptionArray()
        ));
        $fieldsets['version'] = $form->addFieldset('export_format_version_fieldset',
            array(
                'legend' => $helper->__('Export Format Version'),
                'style'  => 'display:none'
            )
        );
        $fieldsets['version']->addField('file_format_version', 'select', array(
            'name'     => 'file_format_version',
            'title'    => $helper->__('Export Format Version'),
            'label'    => $helper->__('Export Format Version'),
            'required' => false,
            'onchange' => 'editForm.handleExportFormatVersionSelector();',
            'values'   => Mage::getModel('Mage_ImportExport_Model_Source_Format_Version')->toOptionArray()
        ));
        $fieldsets['customer'] = $form->addFieldset('customer_entity_fieldset',
            array(
                'legend' => $helper->__('Customer Entity Type'),
                'style'  => 'display:none'
            )
        );
        $fieldsets['customer']->addField('customer_entity', 'select', array(
            'name'     => 'customer_entity',
            'title'    => $helper->__('Customer Entity Type'),
            'label'    => $helper->__('Customer Entity Type'),
            'required' => false,
            'onchange' => 'editForm.handleCustomerEntityTypeSelector();',
            'values'   => Mage::getModel('Mage_ImportExport_Model_Source_Export_Customer_Entity')->toOptionArray()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
