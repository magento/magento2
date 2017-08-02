<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme grid column filter
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Class \Magento\Backend\Block\Widget\Grid\Column\Filter\Theme
 *
 * @since 2.0.0
 */
class Theme extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     * @since 2.0.0
     */
    protected $_labelFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory,
        array $data = []
    ) {
        $this->_labelFactory = $labelFactory;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * Retrieve filter HTML
     *
     * @return string
     * @since 2.0.0
     */
    public function getHtml()
    {
        $options = $this->getOptions();
        if ($this->getColumn()->getWithEmpty()) {
            array_unshift($options, ['value' => '', 'label' => '']);
        }
        $html = sprintf(
            '<select name="%s" id="%s" class="admin__control-select no-changes" %s>%s</select>',
            $this->_getHtmlName(),
            $this->_getHtmlId(),
            $this->getUiId('filter', $this->_getHtmlName()),
            $this->_drawOptions($options)
        );
        return $html;
    }

    /**
     * Retrieve options setted in column.
     * Or load if options was not set.
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptions()
    {
        $options = $this->getColumn()->getOptions();
        if (empty($options) || !is_array($options)) {
            /** @var $label \Magento\Framework\View\Design\Theme\Label */
            $label = $this->_labelFactory->create();
            $options = $label->getLabelsCollection();
        }
        return $options;
    }

    /**
     * Render SELECT options
     *
     * @param array $options
     * @return string
     * @since 2.0.0
     */
    protected function _drawOptions($options)
    {
        if (empty($options) || !is_array($options)) {
            return '';
        }

        $value = $this->getValue();
        $html = '';

        foreach ($options as $option) {
            if (!isset($option['value']) || !isset($option['label'])) {
                continue;
            }
            if (is_array($option['value'])) {
                $html .= '<optgroup label="' . $option['label'] . '">' . $this->_drawOptions(
                    $option['value']
                ) . '</optgroup>';
            } else {
                $selected = $option['value'] == $value && $value !== null ? ' selected="selected"' : '';
                $html .= '<option value="' . $option['value'] . '"' . $selected . '>' . $option['label'] . '</option>';
            }
        }

        return $html;
    }

    /**
     * Retrieve filter condition for collection
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getCondition()
    {
        if ($this->getValue() === null) {
            return null;
        }
        $value = $this->getValue();
        if ($value == 'all') {
            $value = '';
        }
        return ['eq' => $value];
    }
}
