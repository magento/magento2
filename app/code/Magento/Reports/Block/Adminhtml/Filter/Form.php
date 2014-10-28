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
namespace Magento\Reports\Block\Adminhtml\Filter;

/**
 * Adminhtml report filter form
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Report type options
     *
     * @var array
     */
    protected $_reportTypeOptions = array();

    /**
     * Report field visibility
     *
     * @var array
     */
    protected $_fieldVisibility = array();

    /**
     * Report field opions
     *
     * @var array
     */
    protected $_fieldOptions = array();

    /**
     * Set field visibility
     *
     * @param string $fieldId
     * @param bool $visibility
     *
     * @return void
     */
    public function setFieldVisibility($fieldId, $visibility)
    {
        $this->_fieldVisibility[$fieldId] = (bool)$visibility;
    }

    /**
     * Get field visibility
     *
     * @param string $fieldId
     * @param bool $defaultVisibility
     * @return bool
     */
    public function getFieldVisibility($fieldId, $defaultVisibility = true)
    {
        if (!array_key_exists($fieldId, $this->_fieldVisibility)) {
            return $defaultVisibility;
        }
        return $this->_fieldVisibility[$fieldId];
    }

    /**
     * Set field option(s)
     *
     * @param string $fieldId Field id
     * @param mixed $option Field option name
     * @param mixed|null $value Field option value
     *
     * @return void
     */
    public function setFieldOption($fieldId, $option, $value = null)
    {
        if (is_array($option)) {
            $options = $option;
        } else {
            $options = array($option => $value);
        }
        if (!array_key_exists($fieldId, $this->_fieldOptions)) {
            $this->_fieldOptions[$fieldId] = array();
        }
        foreach ($options as $k => $v) {
            $this->_fieldOptions[$fieldId][$k] = $v;
        }
    }

    /**
     * Add report type option
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addReportTypeOption($key, $value)
    {
        $this->_reportTypeOptions[$key] = __($value);
        return $this;
    }

    /**
     * Add fieldset with general report fields
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/sales');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array('data' => array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get'))
        );

        $htmlIdPrefix = 'sales_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Filter')));

        $dateFormat = $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);

        $fieldset->addField('store_ids', 'hidden', array('name' => 'store_ids'));

        $fieldset->addField(
            'report_type',
            'select',
            array('name' => 'report_type', 'options' => $this->_reportTypeOptions, 'label' => __('Match Period To'))
        );

        $fieldset->addField(
            'period_type',
            'select',
            array(
                'name' => 'period_type',
                'options' => array('day' => __('Day'), 'month' => __('Month'), 'year' => __('Year')),
                'label' => __('Period'),
                'title' => __('Period')
            )
        );

        $fieldset->addField(
            'from',
            'date',
            array(
                'name' => 'from',
                'date_format' => $dateFormat,
                'image' => $this->getViewFileUrl('images/grid-cal.gif'),
                'label' => __('From'),
                'title' => __('From'),
                'required' => true
            )
        );

        $fieldset->addField(
            'to',
            'date',
            array(
                'name' => 'to',
                'date_format' => $dateFormat,
                'image' => $this->getViewFileUrl('images/grid-cal.gif'),
                'label' => __('To'),
                'title' => __('To'),
                'required' => true
            )
        );

        $fieldset->addField(
            'show_empty_rows',
            'select',
            array(
                'name' => 'show_empty_rows',
                'options' => array('1' => __('Yes'), '0' => __('No')),
                'label' => __('Empty Rows'),
                'title' => __('Empty Rows')
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Initialize form fields values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _initFormValues()
    {
        $data = $this->getFilterData()->getData();
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                $data[$key] = explode(',', $value[0]);
            }
        }
        $this->getForm()->addValues($data);
        return parent::_initFormValues();
    }

    /**
     * This method is called before rendering HTML
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();

        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
            // apply field visibility
            foreach ($fieldset->getElements() as $field) {
                if (!$this->getFieldVisibility($field->getId())) {
                    $fieldset->removeField($field->getId());
                }
            }
            // apply field options
            foreach ($this->_fieldOptions as $fieldId => $fieldOptions) {
                $field = $fieldset->getElements()->searchById($fieldId);
                /** @var \Magento\Framework\Object $field */
                if ($field) {
                    foreach ($fieldOptions as $k => $v) {
                        $field->setDataUsingMethod($k, $v);
                    }
                }
            }
        }

        return $result;
    }
}
