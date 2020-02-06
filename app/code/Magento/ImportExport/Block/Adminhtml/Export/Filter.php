<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Block\Adminhtml\Export;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Export filter block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
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
     * Local filters types base on attribute code
     *
     * @var \Magento\ImportExport\Helper\Data
     */
    protected $_filterTypeByAttrCode = [
        'updated_at' => \Magento\ImportExport\Model\Export::FILTER_TYPE_DATE,
    ];

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
        array $data = []
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
        $arguments = [
            'name' => $this->getFilterElementName($attribute->getAttributeCode()) . '[]',
            'id' => $this->getFilterElementId($attribute->getAttributeCode()),
            'class' => 'admin__control-text',
            'date_format' => $this->_localeDate->getDateFormat(
                \IntlDateFormatter::SHORT
            ),
        ];
        /** @var $selectBlock \Magento\Framework\View\Element\Html\Date */
        $dateBlock = $this->_layout->createBlock(
            \Magento\Framework\View\Element\Html\Date::class,
            '',
            ['data' => $arguments]
        );
        $fromValue = null;
        $toValue = null;
        if (is_array($value) && count($value) == 2) {
            $fromValue = $this->escapeHtml(reset($value));
            $toValue = $this->escapeHtml(next($value));
        }

        return '<strong class="admin__control-support-text">' . __('From') . ':</strong>&nbsp;'
            . $dateBlock->setValue($fromValue)->getHtml()
            . '&nbsp;<strong class="admin__control-support-text">' . __('To') . ':</strong>&nbsp;'
            . $dateBlock->setId($dateBlock->getId() . '_to')->setValue($toValue)->getHtml();
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
        ) . '" class="admin__control-text input-text input-text-export-filter"';
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
     * @return \Magento\Framework\Phrase|string
     */
    protected function _getMultiSelectHtmlWithValue(Attribute $attribute, $value)
    {
        if ($attribute->getFilterOptions()) {
            $options = $attribute->getFilterOptions();
        } else {
            $options = $attribute->getSource()->getAllOptions();

            foreach ($options as $key => $optionParams) {
                if ('' === $optionParams['value']) {
                    unset($options[$key]);
                    break;
                }
            }
        }

        if ($size = count($options)) {
            $arguments = [
                'name' => $this->getFilterElementName($attribute->getAttributeCode()) . '[]',
                'id' => $this->getFilterElementId($attribute->getAttributeCode()),
                'class' => 'multiselect multiselect-export-filter',
                'extra_params' => 'multiple="multiple" size="' . ($size > 5 ? 5 : ($size < 2 ? 2 : $size)) . '"',
            ];
            /** @var $selectBlock \Magento\Framework\View\Element\Html\Select */
            $selectBlock = $this->_layout->createBlock(
                \Magento\Framework\View\Element\Html\Select::class,
                '',
                ['data' => $arguments]
            );
            return $selectBlock->setOptions($options)->setValue($value)->getHtml();
        } else {
            return __('We can\'t filter an attribute with no attribute options.');
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

        return '<strong class="admin__control-support-text">' .
            $this->getFromAttributePrefix($attribute) .
            ':</strong>&nbsp;' .
            '<input type="text" name="' .
            $name .
            '[]" class="admin__control-text input-text input-text-range"' .
            ' value="' .
            $fromValue .
            '"/>&nbsp;' .
            '<strong class="admin__control-support-text">' .
            __(
                'To'
            ) .
            ':</strong>&nbsp;<input type="text" name="' .
            $name .
            '[]" class="admin__control-text input-text input-text-range" value="' .
            $toValue .
            '" />';
    }

    /**
     * Get 'From' prefix to attribute.
     *
     * @param Attribute $attribute
     * @return \Magento\Framework\Phrase
     * @since 100.2.0
     */
    protected function getFromAttributePrefix(Attribute $attribute)
    {
        $attributePrefix = $attribute->getAttributeCode() === ProductAttributeInterface::CODE_TIER_PRICE
            ? __('Fixed Price: From')
            : __('From');

        return $attributePrefix;
    }

    /**
     * Select field filter HTML with selected value.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _getSelectHtmlWithValue(Attribute $attribute, $value)
    {
        if ($attribute->getFilterOptions()) {
            $options = [];

            foreach ($attribute->getFilterOptions() as $optionValue => $label) {
                $options[] = ['value' => $optionValue, 'label' => $label];
            }
        } else {
            $options = $attribute->getSource()->getAllOptions(false);
        }
        if ($size = count($options)) {
            // add empty value option
            $firstOption = reset($options);

            if ('' === $firstOption['value']) {
                $options[key($options)]['label'] = '';
            } else {
                array_unshift($options, ['value' => '', 'label' => __('-- Not Selected --')]);
            }
            $arguments = [
                'name' => $this->getFilterElementName($attribute->getAttributeCode()),
                'id' => $this->getFilterElementId($attribute->getAttributeCode()),
                'class' => 'admin__control-select select select-export-filter',
            ];
            /** @var $selectBlock \Magento\Framework\View\Element\Html\Select */
            $selectBlock = $this->_layout->createBlock(
                \Magento\Framework\View\Element\Html\Select::class,
                '',
                ['data' => $arguments]
            );
            return $selectBlock->setOptions($options)->setValue($value)->getHtml();
        } else {
            return __('We can\'t filter an attribute with no attribute options.');
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
            [
                'header' => __('Exclude'),
                'type' => 'checkbox',
                'name' => 'skip',
                'field_name' => \Magento\ImportExport\Model\Export::FILTER_ELEMENT_SKIP . '[]',
                'filter' => false,
                'sortable' => false,
                'index' => 'attribute_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id data-grid-checkbox-cell'
            ]
        );
        $this->addColumn(
            'frontend_label',
            [
                'header' => __('Attribute Label'),
                'index' => 'frontend_label',
                'sortable' => false,
                'header_css_class' => 'col-label',
                'column_css_class' => 'col-label'
            ]
        );
        $this->addColumn(
            'attribute_code',
            [
                'header' => __('Attribute Code'),
                'index' => 'attribute_code',
                'header_css_class' => 'col-code',
                'column_css_class' => 'col-code'
            ]
        );
        $this->addColumn(
            'filter',
            [
                'header' => __('Filter'),
                'sortable' => false,
                'filter' => false,
                'frame_callback' => [$this, 'decorateFilter']
            ]
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
     * @param \Magento\Framework\DataObject $column
     * @param boolean $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorateFilter($value, Attribute $row, \Magento\Framework\DataObject $column, $isExport)
    {
        $value = null;
        $values = $column->getValues();
        if (is_array($values) && isset($values[$row->getAttributeCode()])) {
            $value = $values[$row->getAttributeCode()];
        }

        $code = $row->getAttributeCode();
        if (isset($this->_filterTypeByAttrCode[$code])) {
            $filterType =$this->_filterTypeByAttrCode[$code];
        } else {
            $filterType = \Magento\ImportExport\Model\Export::getAttributeFilterType($row);
        }

        switch ($filterType) {
            case \Magento\ImportExport\Model\Export::FILTER_TYPE_SELECT:
                $cell = $this->_getSelectHtmlWithValue($row, $value);
                break;
            case \Magento\ImportExport\Model\Export::FILTER_TYPE_MULTISELECT:
                $cell = $this->_getMultiSelectHtmlWithValue($row, $value);
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * Prepare collection by setting page number, sorting etc..
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function prepareCollection(\Magento\Framework\Data\Collection $collection)
    {
        $this->setCollection($collection);
        return $this->getCollection();
    }
}
