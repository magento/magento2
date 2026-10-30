<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Renderer for sub-heading in fieldset
 */
namespace Magento\Config\Block\System\Config\Form\Field;

/**
 * @api
 * @since 100.0.2
 */
class Heading extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>',
            $element->getHtmlId(),
            $element->getHtmlId(),
            $element->getLabel()
        );
    }
}
