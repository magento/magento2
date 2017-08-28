<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Newchild extends \Magento\Framework\View\Element\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->addClass('element-value-changer');
        $html = '&nbsp;<span class="rule-param rule-param-new-child"' .
            ($element->getParamId() ? ' id="' .
            $element->getParamId() .
            '"' : '') .
            '>';
        $html .= '<a href="javascript:void(0)" class="label">';
        $html .= $element->getValueName();
        $html .= '</a><span class="element">';
        $html .= $element->getElementHtml();
        $html .= '</span></span>&nbsp;';
        return $html;
    }
}
