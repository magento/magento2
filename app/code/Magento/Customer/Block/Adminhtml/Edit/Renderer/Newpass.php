<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Renderer;

use Magento\Backend\Block\AbstractBlock;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Customer new password field renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Newpass extends AbstractBlock implements RendererInterface
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '<div class="field field-' . $element->getHtmlId() . '">';
        $html .= $element->getLabelHtml();
        $html .= '<div class="control">' . $element->getElementHtml();
        $html .= '<div class="nested">';
        $html .= '<div class="field choice">';
        $html .= '<label for="account-send-pass" class="addbefore"><span>' . __('or ') . '</span></label>';
        $html .= '<input type="checkbox" id="account-send-pass" name="' .
            $element->getName() .
            '" value="auto" onclick="setElementDisable(\'' .
            $element->getHtmlId() .
            '\', this.checked)" />';
        $html .= '<label class="label" for="account-send-pass"><span>' . __(
            ' Send auto-generated password'
        ) . '</span></label>';
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";

        return $html;
    }
}
