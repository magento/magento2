<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Custom Options Config Renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Form\Renderer\Config;

use Magento\Backend\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class YearRange extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setStyle('width:70px;')->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = [];
        }

        $from = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $to = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        return __(
            '<label class="label"><span>from</span></label>'
        ) . $from . __(
            '<label class="label"><span>to</span></label>'
        ) . $to;
    }
}
