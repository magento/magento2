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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml cms block edit form
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Store_Delete_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('store_delete_form');
        $this->setTitle(Mage::helper('Mage_Cms_Helper_Data')->__('Block Information'));
    }

    protected function _prepareForm()
    {
        $dataObject = $this->getDataObject();

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $form->setHtmlIdPrefix('store_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('Mage_Core_Helper_Data')->__('Backup Options'), 'class' => 'fieldset-wide'));

        $fieldset->addField('item_id', 'hidden', array(
            'name'  => 'item_id',
            'value' => $dataObject->getId(),
        ));

        $fieldset->addField('create_backup', 'select', array(
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Create DB Backup'),
            'title'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Create DB Backup'),
            'name'      => 'create_backup',
            'options'   => array(
                '1' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Yes'),
                '0' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('No'),
            ),
            'value'     => '1',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
