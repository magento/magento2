<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Field renderer for hidden fields
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field;

class Hidden extends \Magento\Backend\Block\System\Config\Form\Field
{
    /**
     * Decorate field row html to be invisible
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml($element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '" style="display: none;">' . $html . '</tr>';
    }
}
