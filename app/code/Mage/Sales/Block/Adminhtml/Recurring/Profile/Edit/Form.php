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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring profile editing form
 * Can work in scope of product edit form
 */
class Mage_Sales_Block_Adminhtml_Recurring_Profile_Edit_Form extends Mage_Backend_Block_Abstract
{
    /**
     * Reference to the parent element (optional)
     *
     * @var Varien_Data_Form_Element_Abstract
     */
    protected $_parentElement = null;

    /**
     * Whether the form contents can be editable
     *
     * @var bool
     */
    protected $_isReadOnly = false;

    /**
     * Recurring profile instance used for getting labels and options
     *
     * @var Mage_Sales_Model_Recurring_Profile
     */
    protected $_profile = null;

    /**
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    /**
     * Setter for parent element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     */
    public function setParentElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_parentElement = $element;
        return $this;
    }

    /**
     * Setter for current product
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function setProductEntity(Mage_Catalog_Model_Product $product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Instantiate a recurring payment profile to use it as a helper
     */
    protected function _construct()
    {
        $this->_profile = Mage::getSingleton('Mage_Sales_Model_Recurring_Profile');
        return parent::_construct();
    }

    /**
     * Prepare and render the form
     *
     * @return string
     */
    protected function _toHtml()
    {
        // TODO: implement $this->_isReadonly setting
        $form = $this->_prepareForm();
        if ($this->_product && $this->_product->getRecurringProfile()) {
            $form->setValues($this->_product->getRecurringProfile());
        }
        return $form->toHtml();
    }

    /**
     * Instantiate form and fields
     *
     * @return Varien_Data_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->setFieldsetRenderer(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset')
        );
        $form->setFieldsetElementRenderer(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element')
        );

        /**
         * if there is a parent element defined, it will be replaced by a hidden element with the same name
         * and overriden by the form elements
         * It is needed to maintain HTML consistency of the parent element's form
         */
        if ($this->_parentElement) {
            $form->setHtmlIdPrefix($this->_parentElement->getHtmlId())
                ->setFieldNameSuffix($this->_parentElement->getName());
            $form->addField('', 'hidden', array('name' => ''));
        }

        $noYes = array(Mage::helper('Mage_Adminhtml_Helper_Data')->__('No'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Yes'));

        // schedule
        $schedule = $form->addFieldset('schedule_fieldset', array(
            'legend' => Mage::helper('Mage_Sales_Helper_Data')->__('Schedule'),
            'disabled'  => $this->_isReadOnly
        ));
        $schedule->addField('start_date_is_editable', 'select', array(
            'name'    => 'start_date_is_editable',
            'label'   => Mage::helper('Mage_Sales_Helper_Data')->__('Customer Can Define Start Date'),
            'comment' => Mage::helper('Mage_Sales_Helper_Data')->__('Whether buyer can define the date when billing for the profile begins.'),
            'options' => $noYes,
            'disabled' => $this->_isReadOnly
        ));
        $this->_addField($schedule, 'schedule_description');
        $this->_addField($schedule, 'suspension_threshold');
        $this->_addField($schedule, 'bill_failed_later', array('options' => $noYes), 'select');

        // billing
        $billing = $form->addFieldset('billing_fieldset', array(
            'legend' => Mage::helper('Mage_Sales_Helper_Data')->__('Billing'),
            'disabled'  => $this->_isReadOnly
        ));
        $this->_addField($billing, 'period_unit', array(
            'options' => $this->_getPeriodUnitOptions(Mage::helper('Mage_Adminhtml_Helper_Data')->__('-- Please Select --')),
        ), 'select');
        $this->_addField($billing, 'period_frequency');
        $this->_addField($billing, 'period_max_cycles');

        // trial
        $trial = $form->addFieldset('trial_fieldset', array(
            'legend' => Mage::helper('Mage_Sales_Helper_Data')->__('Trial Period'),
            'disabled'  => $this->_isReadOnly
        ));
        $this->_addField($trial, 'trial_period_unit', array(
            'options' => $this->_getPeriodUnitOptions(Mage::helper('Mage_Adminhtml_Helper_Data')->__('-- Not Selected --')),
        ), 'select');
        $this->_addField($trial, 'trial_period_frequency');
        $this->_addField($trial, 'trial_period_max_cycles');
        $this->_addField($trial, 'trial_billing_amount');

        // initial fees
        $initial = $form->addFieldset('initial_fieldset', array(
            'legend' => Mage::helper('Mage_Sales_Helper_Data')->__('Initial Fees'),
            'disabled'  => $this->_isReadOnly
        ));
        $this->_addField($initial, 'init_amount');
        $this->_addField($initial, 'init_may_fail', array('options' => $noYes), 'select');

        return $form;
    }

    /**
     * Add a field to the form or fieldset
     * Form and fieldset have same abstract
     *
     * @param Varien_Data_Form|Varien_Data_Form_Element_Fieldset $formOrFieldset
     * @param string $elementName
     * @param array $options
     * @param string $type
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function _addField($formOrFieldset, $elementName, $options = array(), $type = 'text')
    {
        $options = array_merge($options, array(
            'name'     => $elementName,
            'label'    => $this->_profile->getFieldLabel($elementName),
            'note'     => $this->_profile->getFieldComment($elementName),
            'disabled' => $this->_isReadOnly,
        ));
        if (in_array($elementName, array('period_unit', 'period_frequency'))) {
            $options['required'] = true;
        }
        return $formOrFieldset->addField($elementName, $type, $options);
    }

    /**
     * Getter for period unit options with "Please Select" label
     *
     * @return array
     */
    protected function _getPeriodUnitOptions($emptyLabel)
    {
        return array_merge(array('' => $emptyLabel),
            $this->_profile->getAllPeriodUnits()
        );
    }

    /**
     * Set readonly flag
     *
     * @param boolean $isReadonly
     * @return Mage_Sales_Block_Adminhtml_Recurring_Profile_Edit_Form
     */
    public function setIsReadonly($isReadonly)
    {
        $this->_isReadOnly = $isReadonly;
        return $this;
    }

    /**
     * Get readonly flag
     *
     * @return boolean
     */
    public function getIsReadonly()
    {
        return $this->_isReadOnly;
    }
}
