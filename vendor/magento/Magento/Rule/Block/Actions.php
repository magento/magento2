<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Rule\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Actions implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($element->getRule() && $element->getRule()->getActions()) {
            return $element->getRule()->getActions()->asHtmlRecursive();
        }
        return '';
    }
}
