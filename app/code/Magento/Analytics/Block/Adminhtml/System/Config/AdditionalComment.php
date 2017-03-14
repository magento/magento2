<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

/**
 * Class AdditionalComment.
 *
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
        $html = '<div>' . $element->getLabel() . '</div>';
        $html .= '<div>' . $element->getComment() . '</div>';
        return $this->decirateRowHtml($element, $html);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param $html
     * @return string
     */
    private function decirateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        return sprintf(
            '<tr class="configuration-additional-comment" id="row_%s"><td colspan="3">%s</td></tr>',
            $element->getHtmlId(),
            $html
        );
    }
}
