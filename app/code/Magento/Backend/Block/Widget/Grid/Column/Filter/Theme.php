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
 * Theme grid column filter
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

class Theme extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_labelFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory,
        array $data = array()
    ) {
        $this->_labelFactory = $labelFactory;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * Retrieve filter HTML
     *
     * @return string
     */
    public function getHtml()
    {
        $options = $this->getOptions();
        if ($this->getColumn()->getWithEmpty()) {
            array_unshift($options, array('value' => '', 'label' => ''));
        }
        $html = sprintf(
            '<select name="%s" id="%s" class="no-changes" %s>%s</select>',
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
                $selected = $option['value'] == $value && !is_null($value) ? ' selected="selected"' : '';
                $html .= '<option value="' . $option['value'] . '"' . $selected . '>' . $option['label'] . '</option>';
            }
        }

        return $html;
    }

    /**
     * Retrieve filter condition for collection
     *
     * @return mixed
     */
    public function getCondition()
    {
        if (is_null($this->getValue())) {
            return null;
        }
        $value = $this->getValue();
        if ($value == 'all') {
            $value = '';
        }
        return array('eq' => $value);
    }
}
