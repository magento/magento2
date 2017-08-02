<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class \Magento\Rule\Block\Conditions
 *
 * @since 2.0.0
 */
class Conditions implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    public function render(AbstractElement $element)
    {
        if ($element->getRule() && $element->getRule()->getConditions()) {
            return $element->getRule()->getConditions()->asHtmlRecursive();
        }
        return '';
    }
}
