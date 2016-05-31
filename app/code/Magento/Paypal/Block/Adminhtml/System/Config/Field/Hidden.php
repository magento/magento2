<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Field renderer for hidden fields
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field;

class Hidden extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Decorate field row html to be invisible
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '" style="display: none;">' . $html . '</tr>';
    }
}
