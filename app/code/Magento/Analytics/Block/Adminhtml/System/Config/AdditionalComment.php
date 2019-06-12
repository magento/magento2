<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

/**
 * Provides field with additional information
 */
class AdditionalComment extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<div class="config-additional-comment-title">' . $element->getLabel() . '</div>';
        $html .= '<div class="config-additional-comment-content">' . $element->getComment() . '</div>';
        return $this->decorateRowHtml($element, $html);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param string $html
     * @return string
     */
    private function decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        return sprintf(
            '<tr id="row_%s"><td colspan="3"><div class="config-additional-comment">%s</div></td></tr>',
            $element->getHtmlId(),
            $html
        );
    }
}
