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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Poll edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Rating_Edit_Tab_Form extends Mage_Backend_Block_Widget_Form
{
    /**
     * Application instance
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Backend_Model_Url $urlBuilder
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Model_Session $session
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_App $app
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Layout $layout,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Backend_Model_Url $urlBuilder,
        Mage_Core_Model_Translate $translator,
        Mage_Core_Model_Cache $cache,
        Mage_Core_Model_Design_Package $designPackage,
        Mage_Core_Model_Session $session,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_App $app,
        array $data = array()
    ) {
        $this->_app = $app;
        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $data
        );
    }


    /**
     * Prepare rating edit form
     *
     * @return Mage_Adminhtml_Block_Rating_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('rating_form', array(
            'legend'=>Mage::helper('Mage_Rating_Helper_Data')->__('Rating Title')
        ));

        $fieldset->addField('rating_code', 'text', array(
            'name' => 'rating_code',
            'label' => Mage::helper('Mage_Rating_Helper_Data')->__('Default Value'),
            'class' => 'required-entry',
            'required' => true,
        ));

        foreach (Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreCollection() as $store) {
            $fieldset->addField('rating_code_' . $store->getId(), 'text', array(
                'label' => $store->getName(),
                'name' => 'rating_codes[' . $store->getId() . ']',
            ));
        }

        if (Mage::getSingleton('Mage_Adminhtml_Model_Session')->getRatingData()) {
            $form->setValues(Mage::getSingleton('Mage_Adminhtml_Model_Session')->getRatingData());
            $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getRatingData();
            if (isset($data['rating_codes'])) {
               $this->_setRatingCodes($data['rating_codes']);
            }
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->setRatingData(null);
        } elseif (Mage::registry('rating_data')) {
            $form->setValues(Mage::registry('rating_data')->getData());
            if (Mage::registry('rating_data')->getRatingCodes()) {
               $this->_setRatingCodes(Mage::registry('rating_data')->getRatingCodes());
            }
        }

        if (Mage::registry('rating_data')) {
            $collection = Mage::getModel('Mage_Rating_Model_Rating_Option')
                ->getResourceCollection()
                ->addRatingFilter(Mage::registry('rating_data')->getId())
                ->load();

            $i = 1;
            foreach ($collection->getItems() as $item) {
                $fieldset->addField('option_code_' . $item->getId() , 'hidden', array(
                    'required' => true,
                    'name' => 'option_title[' . $item->getId() . ']',
                    'value' => ($item->getCode()) ? $item->getCode() : $i,
                ));

                $i ++;
            }
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $fieldset->addField('option_code_' . $i, 'hidden', array(
                    'required' => true,
                    'name' => 'option_title[add_' . $i . ']',
                    'value' => $i,
                ));
            }
        }

        $fieldset = $form->addFieldset('visibility_form', array(
            'legend' => Mage::helper('Mage_Rating_Helper_Data')->__('Rating Visibility')
        ));
        if (!$this->_app->isSingleStoreMode()) {
            $field = $fieldset->addField('stores', 'multiselect', array(
                'label' => Mage::helper('Mage_Rating_Helper_Data')->__('Visible In'),
                'name' => 'stores[]',
                'values' => Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreValuesForForm(),
            ));
            $renderer = $this->getLayout()->createBlock('Mage_Backend_Block_Store_Switcher_Form_Renderer_Fieldset_Element');
            $field->setRenderer($renderer);

            if (Mage::registry('rating_data')) {
                $form->getElement('stores')->setValue(Mage::registry('rating_data')->getStores());
            }
        }

        $fieldset->addField('is_active', 'checkbox', array(
            'label' => Mage::helper('Mage_Rating_Helper_Data')->__('Is Active'),
            'name' => 'is_active',
            'value' => 1,
        ));

        $fieldset->addField('position', 'text', array(
            'label' => Mage::helper('Mage_Rating_Helper_Data')->__('Sort Order'),
            'name' => 'position',
        ));

        if (Mage::registry('rating_data')) {
            $form->getElement('position')->setValue(Mage::registry('rating_data')->getPosition());
            $form->getElement('is_active')->setIsChecked(Mage::registry('rating_data')->getIsActive());
        }

        return parent::_prepareForm();
    }

    protected function _setRatingCodes($ratingCodes) {
        foreach($ratingCodes as $store=>$value) {
            if($element = $this->getForm()->getElement('rating_code_' . $store)) {
               $element->setValue($value);
            }
        }
    }

    protected function _toHtml()
    {
        return $this->_getWarningHtml() . parent::_toHtml();
    }

    protected function _getWarningHtml()
    {
        return '<div>
<ul class="messages">
    <li class="notice-msg">
        <ul>
            <li>'.Mage::helper('Mage_Rating_Helper_Data')->__('If you do not specify a rating title for a store, the default value will be used.').'</li>
        </ul>
    </li>
</ul>
</div>';
    }


}
