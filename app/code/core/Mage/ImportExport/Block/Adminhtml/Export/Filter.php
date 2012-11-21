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
 * Export filter block
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Block_Adminhtml_Export_Filter extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Helper object.
     *
     * @var Mage_Core_Helper_Abstract
     */
    protected $_helper;

    /**
     * Set grid parameters.
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_helper = Mage::helper('Mage_ImportExport_Helper_Data');

        $this->setRowClickCallback(null);
        $this->setId('export_filter_grid');
        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('ASC');
        $this->setPagerVisibility(false);
        $this->setDefaultLimit(null);
        $this->setUseAjax(true);
    }

    /**
     * Date 'from-to' filter HTML with values
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getDateFromToHtmlWithValue(Mage_Eav_Model_Entity_Attribute $attribute, $value)
    {
        $arguments = array(
            'name'         => $this->getFilterElementName($attribute->getAttributeCode()) . '[]',
            'id'           => $this->getFilterElementId($attribute->getAttributeCode()),
            'class'        => 'input-text input-text-range-date',
            'date_format'  => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'image'        => $this->getViewFileUrl('images/grid-cal.gif')
        );
        /** @var $selectBlock Mage_Core_Block_Html_Date */
        $dateBlock = $this->_layout->getBlockFactory()->createBlock('Mage_Core_Block_Html_Date', $arguments);
        $fromValue = null;
        $toValue   = null;
        if (is_array($value) && count($value) == 2) {
            $fromValue = $this->_helper->escapeHtml(reset($value));
            $toValue   = $this->_helper->escapeHtml(next($value));
        }


        return '<strong>' . Mage::helper('Mage_ImportExport_Helper_Data')->__('From') . ':</strong>&nbsp;'
            . $dateBlock->setValue($fromValue)->getHtml()
            . '&nbsp;<strong>' . Mage::helper('Mage_ImportExport_Helper_Data')->__('To') . ':</strong>&nbsp;'
            . $dateBlock->setId($dateBlock->getId() . '_to')->setValue($toValue)->getHtml();
    }

    /**
     * Input text filter HTML with value
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getInputHtmlWithValue(Mage_Eav_Model_Entity_Attribute $attribute, $value)
    {
        $html = '<input type="text" name="' . $this->getFilterElementName($attribute->getAttributeCode())
             . '" class="input-text input-text-export-filter"';
        if ($value) {
            $html .= ' value="' . $this->_helper->escapeHtml($value) . '"';
        }

        return $html . ' />';
    }

    /**
     * Multiselect field filter HTML with selected values
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getMultiSelectHtmlWithValue(Mage_Eav_Model_Entity_Attribute $attribute, $value)
    {
        if ($attribute->getFilterOptions()) {
            $options = $attribute->getFilterOptions();
        } else {
            $options = $attribute->getSource()->getAllOptions(false);

            foreach ($options as $key => $optionParams) {
                if ('' === $optionParams['value']) {
                    unset($options[$key]);
                    break;
                }
            }
        }
        if (($size = count($options))) {
            $arguments = array(
                'name'         => $this->getFilterElementName($attribute->getAttributeCode()). '[]',
                'id'           => $this->getFilterElementId($attribute->getAttributeCode()),
                'class'        => 'multiselect multiselect-export-filter',
                'extra_params' => 'multiple="multiple" size="' . ($size > 5 ? 5 : ($size < 2 ? 2 : $size))
            );
            /** @var $selectBlock Mage_Core_Block_Html_Select */
            $selectBlock = $this->_layout->getBlockFactory()->createBlock('Mage_Core_Block_Html_Select', $arguments);
            return $selectBlock->setOptions($options)
                ->setValue($value)
                ->getHtml();
        } else {
            return Mage::helper('Mage_ImportExport_Helper_Data')
                ->__('Attribute does not has options, so filtering is impossible');
        }
    }

    /**
     * Number 'from-to' field filter HTML with selected value.
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getNumberFromToHtmlWithValue(Mage_Eav_Model_Entity_Attribute $attribute, $value)
    {
        $fromValue = null;
        $toValue = null;
        $name = $this->getFilterElementName($attribute->getAttributeCode());
        if (is_array($value) && count($value) == 2) {
            $fromValue = $this->_helper->escapeHtml(reset($value));
            $toValue   = $this->_helper->escapeHtml(next($value));
        }

        return '<strong>' . Mage::helper('Mage_ImportExport_Helper_Data')->__('From') . ':</strong>&nbsp;'
             . '<input type="text" name="' . $name . '[]" class="input-text input-text-range"'
             . ' value="' . $fromValue . '"/>&nbsp;'
             . '<strong>' . Mage::helper('Mage_ImportExport_Helper_Data')->__('To')
             . ':</strong>&nbsp;<input type="text" name="' . $name
             . '[]" class="input-text input-text-range" value="' . $toValue . '" />';
    }

    /**
     * Select field filter HTML with selected value.
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getSelectHtmlWithValue(Mage_Eav_Model_Entity_Attribute $attribute, $value)
    {
        if ($attribute->getFilterOptions()) {
            $options = array();

            foreach ($attribute->getFilterOptions() as $value => $label) {
                $options[] = array('value' => $value, 'label' => $label);
            }
        } else {
            $options = $attribute->getSource()->getAllOptions(false);
        }
        if (($size = count($options))) {
            // add empty vaue option
            $firstOption = reset($options);

            if ('' === $firstOption['value']) {
                $options[key($options)]['label'] = '';
            } else {
                array_unshift($options, array('value' => '', 'label' => ''));
            }
            $arguments = array(
                'name'         => $this->getFilterElementName($attribute->getAttributeCode()),
                'id'           => $this->getFilterElementId($attribute->getAttributeCode()),
                'class'        => 'select select-export-filter'
            );
            /** @var $selectBlock Mage_Core_Block_Html_Select */
            $selectBlock = $this->_layout->getBlockFactory()->createBlock('Mage_Core_Block_Html_Select', $arguments);
            return $selectBlock->setOptions($options)
                ->setValue($value)
                ->getHtml();
        } else {
            return Mage::helper('Mage_ImportExport_Helper_Data')
                ->__('Attribute does not has options, so filtering is impossible');
        }
    }

    /**
     * Add columns to grid
     *
     * @return Mage_ImportExport_Block_Adminhtml_Export_Filter
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('skip', array(
            'header'     => Mage::helper('Mage_ImportExport_Helper_Data')->__('Skip'),
            'type'       => 'checkbox',
            'name'       => 'skip',
            'field_name' => Mage_ImportExport_Model_Export::FILTER_ELEMENT_SKIP . '[]',
            'filter'     => false,
            'sortable'   => false,
            'align'      => 'center',
            'index'      => 'attribute_id'
        ));
        $this->addColumn('frontend_label', array(
            'header'   => Mage::helper('Mage_ImportExport_Helper_Data')->__('Attribute Label'),
            'index'    => 'frontend_label',
            'sortable' => false,
        ));
        $this->addColumn('attribute_code', array(
            'header' => Mage::helper('Mage_ImportExport_Helper_Data')->__('Attribute Code'),
            'index'  => 'attribute_code'
        ));
        $this->addColumn('filter', array(
            'header'         => Mage::helper('Mage_ImportExport_Helper_Data')->__('Filter'),
            'sortable'       => false,
            'filter'         => false,
            'frame_callback' => array($this, 'decorateFilter')
        ));

        if ($this->hasOperation()) {
            $operation = $this->getOperation();
            $skipAttr = $operation->getSkipAttr();
            if ($skipAttr) {
                $this->getColumn('skip')
                    ->setData('values', $skipAttr);
            }
            $filter = $operation->getExportFilter();
            if ($filter) {
                $this->getColumn('filter')
                    ->setData('values', $filter);
            }
        }

        return $this;
    }

    /**
     * Create filter fields for 'Filter' column.
     *
     * @param mixed $value
     * @param Mage_Eav_Model_Entity_Attribute $row
     * @param Varien_Object $column
     * @param boolean $isExport
     * @return string
     */
    public function decorateFilter($value, Mage_Eav_Model_Entity_Attribute $row, Varien_Object $column, $isExport)
    {
        $value  = null;
        $values = $column->getValues();
        if (is_array($values) && isset($values[$row->getAttributeCode()])) {
            $value = $values[$row->getAttributeCode()];
        }
        switch (Mage_ImportExport_Model_Export::getAttributeFilterType($row)) {
            case Mage_ImportExport_Model_Export::FILTER_TYPE_SELECT:
                $cell = $this->_getSelectHtmlWithValue($row, $value);
                break;
            case Mage_ImportExport_Model_Export::FILTER_TYPE_INPUT:
                $cell = $this->_getInputHtmlWithValue($row, $value);
                break;
            case Mage_ImportExport_Model_Export::FILTER_TYPE_DATE:
                $cell = $this->_getDateFromToHtmlWithValue($row, $value);
                break;
            case Mage_ImportExport_Model_Export::FILTER_TYPE_NUMBER:
                $cell = $this->_getNumberFromToHtmlWithValue($row, $value);
                break;
            default:
                $cell = Mage::helper('Mage_ImportExport_Helper_Data')->__('Unknown attribute filter type');
        }
        return $cell;
    }

    /**
     * Element filter ID getter.
     *
     * @param string $attributeCode
     * @return string
     */
    public function getFilterElementId($attributeCode)
    {
        return Mage_ImportExport_Model_Export::FILTER_ELEMENT_GROUP . "_{$attributeCode}";
    }

    /**
     * Element filter full name getter.
     *
     * @param string $attributeCode
     * @return string
     */
    public function getFilterElementName($attributeCode)
    {
        return Mage_ImportExport_Model_Export::FILTER_ELEMENT_GROUP . "[{$attributeCode}]";
    }

    /**
     * Get row edit URL.
     *
     * @return string|boolean
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * Prepare collection by setting page number, sorting etc..
     *
     * @param Varien_Data_Collection $collection
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Collection
     */
    public function prepareCollection(Varien_Data_Collection $collection)
    {
        $this->setCollection($collection);
        return $this->getCollection();
    }
}
