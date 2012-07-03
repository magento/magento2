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
 * Import edit form block
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Block_Adminhtml_Import_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Add fieldsets
     *
     * @return Mage_ImportExport_Block_Adminhtml_Import_Edit_Form
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('Mage_ImportExport_Helper_Data');
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/validate'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        // base fieldset
        $fieldsets['base'] = $form->addFieldset('base_fieldset', array('legend' => $helper->__('Import Settings')));
        $fieldsets['base']->addField('entity', 'select', array(
            'name'     => 'entity',
            'title'    => $helper->__('Entity Type'),
            'label'    => $helper->__('Entity Type'),
            'required' => true,
            'onchange' => 'editForm.handleEntityTypeSelector();',
            'values'   => Mage::getModel('Mage_ImportExport_Model_Source_Import_Entity')->toOptionArray()
        ));
        $fieldsets['base']->addField('behavior', 'select', array(
            'name'     => 'behavior',
            'title'    => $helper->__('Import Behavior'),
            'label'    => $helper->__('Import Behavior'),
            'required' => true,
            'values'   => Mage::getModel('Mage_ImportExport_Model_Source_Import_Behavior')->toOptionArray()
        ));

        // fieldset for format version
        $fieldsets['version'] = $form->addFieldset('import_format_version_fieldset',
            array(
                'legend' => $helper->__('Import Format Version'),
                'style'  => 'display:none'
            )
        );
        $fieldsets['version']->addField('file_format_version', 'select', array(
            'name'     => 'file_format_version',
            'title'    => $helper->__('Import Format Version'),
            'label'    => $helper->__('Import Format Version'),
            'required' => true,
            'onchange' => 'editForm.handleImportFormatVersionSelector();',
            'values'   => Mage::getModel('Mage_ImportExport_Model_Source_Format_Version')->toOptionArray()
        ));

        // fieldset for customer entity
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
            'disabled' => true,
            'values'   => Mage::getModel('Mage_ImportExport_Model_Source_Import_Customer_Entity')->toOptionArray()
        ));

        // fieldset for file uploading
        $fieldsets['upload'] = $form->addFieldset('upload_file_fieldset',
            array(
                'legend' => $helper->__('File to Import'),
                'style'  => 'display:none'
            )
        );
        $fieldsets['upload']->addField(Mage_ImportExport_Model_Import::FIELD_NAME_SOURCE_FILE, 'file', array(
            'name'     => Mage_ImportExport_Model_Import::FIELD_NAME_SOURCE_FILE,
            'label'    => $helper->__('Select File to Import'),
            'title'    => $helper->__('Select File to Import'),
            'required' => true
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
