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
namespace Magento\ImportExport\Block\Adminhtml\Export;

use Magento\Eav\Model\Entity\Attribute;

/**
 * Export filter block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Filter extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Helper object.
     *
     * @var \Magento\Framework\App\Helper\AbstractHelper
     */
    protected $_helper;

    /**
     * Import export data
     *
     * @var \Magento\ImportExport\Helper\Data
     */
    protected $_importExportData = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        array $data = array()
    ) {
        $this->_importExportData = $importExportData;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid parameters.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_helper = $this->_importExportData;

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
     * @param Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getDateFromToHtmlWithValue(Attribute $attribute, $value)
    {
        $arguments = array(
            'name' => $this->getFilterElementName($attribute->getAttributeCode()) . '[]',
            'id' => $this->getFilterElementId($attribute->getAttributeCode()),
            'class' => 'input-text input-text-range-date',
            'date_format' => $this->_localeDate->getDateFormat(
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
            ),
            'image' => $this->getViewFileUrl('images/grid-cal.gif')
        );
        /** @var $selectBlock \Magento\Framework\View\Element\Html\Date */
        $dateBlock = $this->_layout->getBlockFactory()->createBlock(
            'Magento\Framework\View\Element\Html\Date',
            array('data' => $arguments)
        );
        $fromValue = null;
        $toValue = null;
        if (is_array($value) && count($value) == 2) {
            $fromValue = $this->escapeHtml(reset($value));
            $toValue = $this->escapeHtml(next($value));
        }

        return '<strong>' . __(
            'From'
        ) . ':</strong>&nbsp;' . $dateBlock->setValue(
            $fromValue
        )->getHtml() . '&nbsp;<strong>' . __(
            'To'
        ) . ':</strong>&nbsp;' . $dateBlock->setId(
            $dateBlock->getId() . '_to'
        )->setValue(
            $toValue
        )->getHtml();
    }

    /**
     * Input text filter HTML with value
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getInputHtmlWithValue(Attribute $attribute, $value)
    {
        $html = '<input type="text" name="' . $this->getFilterElementName(
            $attribute->getAttributeCode()
        ) . '" class="input-text input-text-export-filter"';
        if ($value) {
            $html .= ' value="' . $this->escapeHtml($value) . '"';
        }
        return $html . ' />';
    }

    /**
     * Multiselect field filter HTML with selected values
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getMultiSelectHtmlWithValue(Attribute $attribute, $value)
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
        if ($size = count($options)) {
            $arguments = array(
                'name' => $this->getFilterElementName($attribute->getAttributeCode()) . '[]',
                'id' => $this->getFilterElementId($attribute->getAttributeCode()),
                'class' => 'multiselect multiselect-export-filter',
                'extra_params' => 'multiple="multiple" size="' . ($size > 5 ? 5 : ($size < 2 ? 2 : $size))
            );
            /** @var $selectBlock \Magento\Framework\View\Element\Html\Select */
            $selectBlock = $this->_layout->getBlockFactory()->createBlock(
                'Magento\Framework\View\Element\Html\Select',
                array('data' => $arguments)
            );
            return $selectBlock->setOptions($options)->setValue($value)->getHtml();
        } else {
            return __('Attribute does not has options, so filtering is impossible');
        }
    }

    /**
     * Number 'from-to' field filter HTML with selected value.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getNumberFromToHtmlWithValue(Attribute $attribute, $value)
    {
        $fromValue = null;
        $toValue = null;
        $name = $this->getFilterElementName($attribute->getAttributeCode());
        if (is_array($value) && count($value) == 2) {
            $fromValue = $this->escapeHtml(reset($value));
            $toValue = $this->escapeHtml(next($value));
        }

        return '<strong>' . __(
            'From'
        ) .
        ':</strong>&nbsp;' .
        '<input type="text" name="' .
        $name .
        '[]" class="input-text input-text-range"' .
        ' value="' .
        $fromValue .
        '"/>&nbsp;' .
        '<strong>' .
        __(
            'To'
        ) .
        ':</strong>&nbsp;<input type="text" name="' .
        $name .
        '[]" class="input-text input-text-range" value="' .
        $toValue .
        '" />';
    }

    /**
     * Select field filter HTML with selected value.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getSelectHtmlWithValue(Attribute $attribute, $value)
    {
        if ($attribute->getFilterOptions()) {
            $options = array();

            foreach ($attribute->getFilterOptions() as $value => $label) {
                $options[] = array('value' => $value, 'label' => $label);
            }
        } else {
            $options = $attribute->getSource()->getAllOptions(false);
        }
        if ($size = count($options)) {
            // add empty vaue option
            $firstOption = reset($options);

            if ('' === $firstOption['value']) {
                $options[key($options)]['label'] = '';
            } else {
                array_unshift($options, array('value' => '', 'label' => ''));
            }
            $arguments = array(
                'name' => $this->getFilterElementName($attribute->getAttributeCode()),
                'id' => $this->getFilterElementId($attribute->getAttributeCode()),
                'class' => 'select select-export-filter'
            );
            /** @var $selectBlock \Magento\Framework\View\Element\Html\Select */
            $selectBlock = $this->_layout->getBlockFactory()->createBlock(
                'Magento\Framework\View\Element\Html\Select',
                array('data' => $arguments)
            );
            return $selectBlock->setOptions($options)->setValue($value)->getHtml();
        } else {
            return __('Attribute does not has options, so filtering is impossible');
        }
    }

    /**
     * Add columns to grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'skip',
            array(
                'header' => __('Exclude'),
                'type' => 'checkbox',
                'name' => 'skip',
                'field_name' => \Magento\ImportExport\Model\Export::FILTER_ELEMENT_SKIP . '[]',
                'filter' => false,
                'sortable' => false,
                'index' => 'attribute_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            )
        );
        $this->addColumn(
            'frontend_label',
            array(
                'header' => __('Attribute Label'),
                'index' => 'frontend_label',
                'sortable' => false,
                'header_css_class' => 'col-label',
                'column_css_class' => 'col-label'
            )
        );
        $this->addColumn(
            'attribute_code',
            array(
                'header' => __('Attribute Code'),
                'index' => 'attribute_code',
                'header_css_class' => 'col-code',
                'column_css_class' => 'col-code'
            )
        );
        $this->addColumn(
            'filter',
            array(
                'header' => __('Filter'),
                'sortable' => false,
                'filter' => false,
                'frame_callback' => array($this, 'decorateFilter')
            )
        );

        if ($this->hasOperation()) {
            $operation = $this->getOperation();
            $skipAttr = $operation->getSkipAttr();
            if ($skipAttr) {
                $this->getColumn('skip')->setData('values', $skipAttr);
            }
            $filter = $operation->getExportFilter();
            if ($filter) {
                $this->getColumn('filter')->setData('values', $filter);
            }
        }

        return $this;
    }

    /**
     * Create filter fields for 'Filter' column.
     *
     * @param mixed $value
     * @param Attribute $row
     * @param \Magento\Framework\Object $column
     * @param boolean $isExport
     * @return string
     */
    public function decorateFilter($value, Attribute $row, \Magento\Framework\Object $column, $isExport)
    {
        $value = null;
        $values = $column->getValues();
        if (is_array($values) && isset($values[$row->getAttributeCode()])) {
            $value = $values[$row->getAttributeCode()];
        }
        switch (\Magento\ImportExport\Model\Export::getAttributeFilterType($row)) {
            case \Magento\ImportExport\Model\Export::FILTER_TYPE_SELECT:
                $cell = $this->_getSelectHtmlWithValue($row, $value);
                break;
            case \Magento\ImportExport\Model\Export::FILTER_TYPE_INPUT:
                $cell = $this->_getInputHtmlWithValue($row, $value);
                break;
            case \Magento\ImportExport\Model\Export::FILTER_TYPE_DATE:
                $cell = $this->_getDateFromToHtmlWithValue($row, $value);
                break;
            case \Magento\ImportExport\Model\Export::FILTER_TYPE_NUMBER:
                $cell = $this->_getNumberFromToHtmlWithValue($row, $value);
                break;
            default:
                $cell = __('Unknown attribute filter type');
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
        return \Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP . "_{$attributeCode}";
    }

    /**
     * Element filter full name getter.
     *
     * @param string $attributeCode
     * @return string
     */
    public function getFilterElementName($attributeCode)
    {
        return \Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP . "[{$attributeCode}]";
    }

    /**
     * Get row edit URL.
     *
     * @param Attribute $row
     * @return string|false
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * Prepare collection by setting page number, sorting etc..
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return \Magento\Eav\Model\Resource\Entity\Attribute\Collection
     */
    public function prepareCollection(\Magento\Framework\Data\Collection $collection)
    {
        $this->setCollection($collection);
        return $this->getCollection();
    }
}
