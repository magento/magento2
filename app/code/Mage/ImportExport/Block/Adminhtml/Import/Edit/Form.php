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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/validate'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ));

        // base fieldset
        /** @var $importEntity Mage_ImportExport_Model_Source_Import_Entity */
        $importEntity = Mage::getModel('Mage_ImportExport_Model_Source_Import_Entity');
        $fieldsets['base'] = $form->addFieldset('base_fieldset', array('legend' => $this->__('Import Settings')));
        $fieldsets['base']->addField('entity', 'select', array(
            'name'     => 'entity',
            'title'    => $this->__('Entity Type'),
            'label'    => $this->__('Entity Type'),
            'required' => true,
            'onchange' => 'varienImport.handleEntityTypeSelector();',
            'values'   => $importEntity->toOptionArray(),
        ));

        // add behaviour fieldsets
        $uniqueBehaviors = Mage_ImportExport_Model_Import::getUniqueEntityBehaviors();
        foreach ($uniqueBehaviors as $behaviorCode => $behaviorClass) {
            $fieldsets[$behaviorCode] = $form->addFieldset(
                $behaviorCode . '_fieldset',
                array(
                    'legend' => $this->__('Import Behavior'),
                    'class'  => 'no-display',
                )
            );
            /** @var $behaviorSource Mage_ImportExport_Model_Source_Import_BehaviorAbstract */
            $behaviorSource = Mage::getModel($behaviorClass);
            $fieldsets[$behaviorCode]->addField($behaviorCode, 'select', array(
                'name'     => 'behavior',
                'title'    => $this->__('Import Behavior'),
                'label'    => $this->__('Import Behavior'),
                'required' => true,
                'disabled' => true,
                'values'   => $behaviorSource->toOptionArray(),
            ));
        }

        // fieldset for file uploading
        $fieldsets['upload'] = $form->addFieldset('upload_file_fieldset',
            array(
                'legend' => $this->__('File to Import'),
                'class'  => 'no-display',
            )
        );
        $fieldsets['upload']->addField(Mage_ImportExport_Model_Import::FIELD_NAME_SOURCE_FILE, 'file', array(
            'name'     => Mage_ImportExport_Model_Import::FIELD_NAME_SOURCE_FILE,
            'label'    => $this->__('Select File to Import'),
            'title'    => $this->__('Select File to Import'),
            'required' => true,
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
