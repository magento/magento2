<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface AttributeInterface
 */
interface AttributeInterface
{
    /**
     * Compiles the Element node
     *
     * @param \DOMAttr $node
     * @param UiComponentInterface $component
     * @return void
     */
    public function compile(\DOMAttr $node, UiComponentInterface $component);
}
