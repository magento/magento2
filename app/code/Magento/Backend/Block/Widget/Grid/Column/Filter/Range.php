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
 * Range grid column filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

class Range extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    /**
     * @return string
     */
    public function getHtml()
    {
        $html = '<div class="range"><div class="range-line">' .
            '<input type="text" name="' .
            $this->_getHtmlName() .
            '[from]" id="' .
            $this->_getHtmlId() .
            '_from" placeholder="' .
            __(
                'From'
            ) . '" value="' . $this->getEscapedValue(
                'from'
            ) . '" class="input-text no-changes" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'from'
            ) . '/></div>';
        $html .= '<div class="range-line">' .
            '<input type="text" name="' .
            $this->_getHtmlName() .
            '[to]" id="' .
            $this->_getHtmlId() .
            '_to" placeholder="' .
            __(
                'To'
            ) . '" value="' . $this->getEscapedValue(
                'to'
            ) . '" class="input-text no-changes" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'to'
            ) . '/></div></div>';
        return $html;
    }

    /**
     * @param string|null $index
     * @return mixed
     */
    public function getValue($index = null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }
        $value = $this->getData('value');
        if (isset($value['from']) && strlen($value['from']) > 0 || isset($value['to']) && strlen($value['to']) > 0) {
            return $value;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCondition()
    {
        $value = $this->getValue();
        return $value;
    }
}
