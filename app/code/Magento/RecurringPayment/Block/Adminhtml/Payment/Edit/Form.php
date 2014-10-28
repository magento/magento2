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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring payment editing form
 * Can work in scope of product edit form
 */
namespace Magento\RecurringPayment\Block\Adminhtml\Payment\Edit;

class Form extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * Reference to the parent element (optional)
     *
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_parentElement = null;

    /**
     * Whether the form contents can be editable
     *
     * @var bool
     */
    protected $_isReadOnly = false;

    /**
     * Recurring payment instance used for getting labels and options
     *
     * @var \Magento\RecurringPayment\Block\Fields
     */
    protected $_recurringPaymentFields;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product = null;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\RecurringPayment\Model\PeriodUnits
     */
    protected $_periodUnits;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\RecurringPayment\Block\Fields $recurringPaymentFields
     * @param \Magento\RecurringPayment\Model\PeriodUnits $periodUnits
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\RecurringPayment\Block\Fields $recurringPaymentFields,
        \Magento\RecurringPayment\Model\PeriodUnits $periodUnits,
        array $data = array()
    ) {
        $this->_formFactory = $formFactory;
        $this->_recurringPaymentFields = $recurringPaymentFields;
        parent::__construct($context, $data);
        $this->_periodUnits = $periodUnits;
    }

    /**
     * Setter for parent element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return $this
     */
    public function setParentElement(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_parentElement = $element;
        return $this;
    }

    /**
     * Setter for current product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProductEntity(\Magento\Catalog\Model\Product $product)
    {
        $this->_product = $product;
        return $this;
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
        if ($this->_product && $this->_product->getRecurringPayment()) {
            $form->setValues($this->_product->getRecurringPayment());
        }
        return $form->toHtml();
    }

    /**
     * Instantiate form and fields
     *
     * @return \Magento\Framework\Data\Form
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $form->setFieldsetRenderer(
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Renderer\Fieldset')
        );
        $form->setFieldsetElementRenderer(
            $this->getLayout()->createBlock('Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element')
        );

        /**
         * if there is a parent element defined, it will be replaced by a hidden element with the same name
         * and overridden by the form elements
         * It is needed to maintain HTML consistency of the parent element's form
         */
        if ($this->_parentElement) {
            $form->setHtmlIdPrefix(
                $this->_parentElement->getHtmlId()
            )->setFieldNameSuffix(
                $this->_parentElement->getName()
            );
            $form->addField('', 'hidden', array('name' => ''));
        }

        $noYes = array(__('No'), __('Yes'));

        // schedule
        $schedule = $form->addFieldset(
            'schedule_fieldset',
            array('legend' => __('Schedule'), 'disabled' => $this->_isReadOnly)
        );
        $schedule->addField(
            'start_date_is_editable',
            'select',
            array(
                'name' => 'start_date_is_editable',
                'label' => __('Customer Can Define Start Date'),
                'comment' => __('Select whether buyer can define the date when billing for the payment begins.'),
                'options' => $noYes,
                'disabled' => $this->_isReadOnly
            )
        );
        $this->_addField($schedule, 'schedule_description');
        $this->_addField($schedule, 'suspension_threshold');
        $this->_addField($schedule, 'bill_failed_later', array('options' => $noYes), 'select');

        // billing
        $billing = $form->addFieldset(
            'billing_fieldset',
            array('legend' => __('Billing'), 'disabled' => $this->_isReadOnly)
        );
        $this->_addField(
            $billing,
            'period_unit',
            array('options' => $this->_getPeriodUnitOptions(__('-- Please Select --'))),
            'select'
        );
        $this->_addField($billing, 'period_frequency');
        $this->_addField($billing, 'period_max_cycles');

        // trial
        $trial = $form->addFieldset(
            'trial_fieldset',
            array('legend' => __('Trial Period'), 'disabled' => $this->_isReadOnly)
        );
        $this->_addField(
            $trial,
            'trial_period_unit',
            array('options' => $this->_getPeriodUnitOptions(__('-- Not Selected --'))),
            'select'
        );
        $this->_addField($trial, 'trial_period_frequency');
        $this->_addField($trial, 'trial_period_max_cycles');
        $this->_addField($trial, 'trial_billing_amount');

        // initial fees
        $initial = $form->addFieldset(
            'initial_fieldset',
            array('legend' => __('Initial Fees'), 'disabled' => $this->_isReadOnly)
        );
        $this->_addField($initial, 'init_amount');
        $this->_addField($initial, 'init_may_fail', array('options' => $noYes), 'select');

        return $form;
    }

    /**
     * Add a field to the form or fieldset
     * Form and fieldset have same abstract
     *
     * @param \Magento\Framework\Data\Form|\Magento\Framework\Data\Form\Element\Fieldset $formOrFieldset
     * @param string $elementName
     * @param array $options
     * @param string $type
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected function _addField($formOrFieldset, $elementName, $options = array(), $type = 'text')
    {
        $options = array_merge(
            $options,
            array(
                'name' => $elementName,
                'label' => $this->_recurringPaymentFields->getFieldLabel($elementName),
                'note' => $this->_recurringPaymentFields->getFieldComment($elementName),
                'disabled' => $this->_isReadOnly
            )
        );
        if (in_array($elementName, array('period_unit', 'period_frequency'))) {
            $options['required'] = true;
        }
        return $formOrFieldset->addField($elementName, $type, $options);
    }

    /**
     * Getter for period unit options with "Please Select" label
     *
     * @param string $emptyLabel
     * @return array
     */
    protected function _getPeriodUnitOptions($emptyLabel)
    {
        return array_merge(array('' => $emptyLabel), $this->_periodUnits->toOptionArray());
    }

    /**
     * Set readonly flag
     *
     * @param boolean $isReadonly
     * @return \Magento\RecurringPayment\Block\Adminhtml\Payment\Edit\Form
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsReadonly()
    {
        return $this->_isReadOnly;
    }
}
