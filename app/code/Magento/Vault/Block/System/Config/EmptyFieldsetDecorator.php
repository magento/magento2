<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;

class EmptyFieldsetDecorator extends Fieldset
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $childrenHtml = $this->_getChildrenElementsHtml($element);
        if (empty($childrenHtml)) {
            return '';
        }

        return parent::render($element);
    }
}
