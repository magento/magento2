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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * URL rewrites edit form
 *
 * @method Mage_Core_Model_Url_Rewrite getUrlRewrite()
 * @method Mage_Adminhtml_Block_Urlrewrite_Edit_Form setUrlRewrite(Mage_Core_Model_Url_Rewrite $model)
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Urlrewrite_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @var array
     */
    protected $_sessionData = null;

    /**
     * @var array
     */
    protected $_allStores = null;

    /**
     * @var bool
     */
    protected $_requireStoresFilter = false;

    /**
     * @var array
     */
    protected $_formValues = array();

    /**
     * Set form id and title
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('urlrewrite_form');
        $this->setTitle(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Block Information'));
    }

    /**
     * Initialize form values
     * Set form data either from model values or from session
     *
     * @return Mage_Adminhtml_Block_Urlrewrite_Edit_Form
     */
    protected function _initFormValues()
    {
        $model = $this->_getModel();
        $this->_formValues = array(
            'store_id'     => $model->getStoreId(),
            'id_path'      => $model->getIdPath(),
            'request_path' => $model->getRequestPath(),
            'target_path'  => $model->getTargetPath(),
            'options'      => $model->getOptions(),
            'description'  => $model->getDescription(),
        );

        $sessionData = $this->_getSessionData();
        if ($sessionData) {
            foreach (array_keys($this->_formValues) as $key) {
                if (isset($sessionData[$key])) {
                    $this->_formValues[$key] = $sessionData[$key];
                }
            }
        }

        return $this;
    }

    /**
     * Prepare the form layout
     *
     * @return Mage_Adminhtml_Block_Urlrewrite_Edit_Form
     */
    protected function _prepareForm()
    {
        $this->_initFormValues();

        // Prepare form
        $form = new Varien_Data_Form(array(
            'id'            => 'edit_form',
            'use_container' => true,
            'method'        => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('URL Rewrite Information')
        ));

        /** @var $typesModel Mage_Core_Model_Source_Urlrewrite_Types */
        $typesModel = Mage::getModel('Mage_Core_Model_Source_Urlrewrite_Types');
        $fieldset->addField('is_system', 'select', array(
            'label'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Type'),
            'title'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Type'),
            'name'     => 'is_system',
            'required' => true,
            'options'  => $typesModel->getAllOptions(),
            'disabled' => true,
            'value'    => $this->_getModel()->getIsSystem()
        ));

        $fieldset->addField('id_path', 'text', array(
            'label'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('ID Path'),
            'title'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('ID Path'),
            'name'     => 'id_path',
            'required' => true,
            'disabled' => false,
            'value'    => $this->_formValues['id_path']
        ));

        $fieldset->addField('request_path', 'text', array(
            'label'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Request Path'),
            'title'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Request Path'),
            'name'     => 'request_path',
            'required' => true,
            'value'    => $this->_formValues['request_path']
        ));

        $fieldset->addField('target_path', 'text', array(
            'label'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Target Path'),
            'title'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Target Path'),
            'name'     => 'target_path',
            'required' => true,
            'disabled' => false,
            'value'    => $this->_formValues['target_path'],
        ));

        /** @var $optionsModel Mage_Core_Model_Source_Urlrewrite_Options */
        $optionsModel = Mage::getModel('Mage_Core_Model_Source_Urlrewrite_Options');
        $fieldset->addField('options', 'select', array(
            'label'   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Redirect'),
            'title'   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Redirect'),
            'name'    => 'options',
            'options' => $optionsModel->getAllOptions(),
            'value'   => $this->_formValues['options']
        ));

        $fieldset->addField('description', 'textarea', array(
            'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Description'),
            'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Description'),
            'name'  => 'description',
            'cols'  => 20,
            'rows'  => 5,
            'value' => $this->_formValues['description'],
            'wrap'  => 'soft'
        ));

        $this->_prepareStoreElement($fieldset);

        $this->setForm($form);
        $this->_formPostInit($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare store element
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     */
    protected function _prepareStoreElement($fieldset)
    {
        // get store switcher or a hidden field with it's id
        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'hidden', array(
                'name'  => 'store_id',
                'value' => Mage::app()->getStore(true)->getId()
            ), 'id_path');
        } else {
            /** @var $renderer Mage_Backend_Block_Store_Switcher_Form_Renderer_Fieldset_Element */
            $renderer = $this->getLayout()
                ->createBlock('Mage_Backend_Block_Store_Switcher_Form_Renderer_Fieldset_Element');

            $storeElement = $fieldset->addField('store_id', 'select', array(
                'label'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Store'),
                'title'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Store'),
                'name'     => 'store_id',
                'required' => true,
                'values'   => $this->_getRestrictedStoresList(),
                'disabled' => $this->_getModel()->getIsSystem(),
                'value'    => $this->_formValues['store_id'],
            ), 'id_path');
            $storeElement->setRenderer($renderer);
        }
    }

    /**
     * Form post init
     *
     * @param Varien_Data_Form $form
     * @return Mage_Adminhtml_Block_Urlrewrite_Edit_Form
     */
    protected function _formPostInit($form)
    {
        $form->setAction(
            Mage::helper('Mage_Adminhtml_Helper_Data')->getUrl('*/*/save', array(
                'id' => $this->_getModel()->getId()
            ))
        );

        return $this;
    }

    /**
     * Get session data
     *
     * @return array
     */
    protected function _getSessionData()
    {
        if (is_null($this->_sessionData)) {
            $this->_sessionData = Mage::getModel('Mage_Adminhtml_Model_Session')->getData('urlrewrite_data', true);
        }
        return $this->_sessionData;
    }

    /**
     * Get URL rewrite model instance
     *
     * @return Mage_Core_Model_Url_Rewrite
     */
    protected function _getModel()
    {
        if (!$this->hasData('url_rewrite')) {
            $this->setUrlRewrite(Mage::getModel('Mage_Core_Model_Url_Rewrite'));
        }
        return $this->getUrlRewrite();
    }

    /**
     * Get request stores
     *
     * @return array
     */
    protected function _getAllStores()
    {
        if (is_null($this->_allStores)) {
            $this->_allStores = Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreValuesForForm();
        }

        return $this->_allStores;
    }

    /**
     * Get entity stores
     *
     * @return array
     */
    protected function _getEntityStores()
    {
        return $this->_getAllStores();
    }

    /**
     * Get restricted stores list
     * Stores should be filtered only if custom entity is specified.
     * If we use custom rewrite, all stores are accepted.
     *
     * @return array
     */
    protected function _getRestrictedStoresList()
    {
        $stores = $this->_getAllStores();
        $entityStores = $this->_getEntityStores();
        $stores = $this->_getStoresListRestrictedByEntityStores($stores, $entityStores);

        return $stores;
    }

    /**
     * Get stores list restricted by entity stores
     *
     * @param array $stores
     * @param array $entityStores
     * @return array
     */
    private function _getStoresListRestrictedByEntityStores(array $stores, array $entityStores)
    {
        if ($this->_requireStoresFilter) {
            foreach ($stores as $i => $store) {
                if (isset($store['value']) && $store['value']) {
                    $found = false;
                    foreach ($store['value'] as $k => $v) {
                        if (isset($v['value']) && in_array($v['value'], $entityStores)) {
                            $found = true;
                        } else {
                            unset($stores[$i]['value'][$k]);
                        }
                    }
                    if (!$found) {
                        unset($stores[$i]);
                    }
                }
            }
        }

        return $stores;
    }
}
