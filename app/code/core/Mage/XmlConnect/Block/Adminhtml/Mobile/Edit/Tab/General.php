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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tab for General Info Management
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare form before rendering HTML
     * Setting Form Fieldsets and fields
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication();

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('app_');
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $this->__('App Information')));

        if ($model->getId()) {
            $fieldset->addField('application_id', 'hidden', array(
                'name'  => 'application_id',
                'value' => $model->getId()
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => $this->__('App Name'),
            'title'     => $this->__('App Name'),
            'maxlength' => '250',
            'value'     => $model->getName(),
            'required'  => true,
        ));

        if ($model->getId()) {
            $fieldset->addField('code', 'label', array(
                'label' => $this->__('App Code'),
                'title' => $this->__('App Code'),
                'value' => $model->getCode(),
            ));
        }

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $storeElement = $fieldset->addField('store_id', 'select', array(
                'name'      => 'store_id',
                'label'     => $this->__('Store View'),
                'title'     => $this->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreValuesForForm(false, false),
            ));
        } else {
            $storeElement = $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'store_id',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        if ($model->getId()) {
            $storeElement->setValue($model->getStoreId());
            $storeElement->setDisabled(true);
        } else if ($model->getStoreId()) {
            $storeElement->setValue($model->getStoreId());
        }

        $fieldset->addField('showdev', 'select', array(
                'name'      => 'showdev',
                'label'     => $this->__('Device Type'),
                'title'     => $this->__('Device Type'),
                'values'    => array($model->getType() => $model->getDevtype()),
                'disabled'  => true,
        ));

        $fieldset->addField('devtype', 'hidden', array(
                'name'  => 'devtype',
                'value' => $model->getType(),
        ));

        $yesNoValues = Mage::getModel('Mage_Adminhtml_Model_System_Config_Source_Yesno')->toOptionArray();

        $fieldset->addField('browsing_mode', 'select', array(
            'label'     => $this->__('Catalog Only App?'),
            'name'      => 'browsing_mode',
            'note'      => $this->__('A Catalog Only App will not support functions such as add to cart, add to wishlist, or login.'),
            'value'     => $model->getBrowsingMode(),
            'values'    => $yesNoValues
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Tab label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('General');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('General');
    }

    /**
     * Check if tab can be shown
     *
     * @return bool
     */
    public function canShowTab()
    {
        return (bool) !Mage::getSingleton('Mage_Adminhtml_Model_Session')->getNewApplication();
    }

    /**
     * Check if tab hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
