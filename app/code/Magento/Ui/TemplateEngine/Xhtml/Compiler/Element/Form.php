<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler\Element;

use Magento\Framework\Object;
use Magento\Ui\TemplateEngine\Xhtml\Compiler;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Form
 */
class Form implements ElementInterface
{
    /**
     * Compiles the Element node
     *
     * @param Compiler $compiler
     * @param \DOMElement $node
     * @param UiComponentInterface $component
     * @param Object $context
     * @return void
     */
    public function compile(
        Compiler $compiler,
        \DOMElement $node,
        UiComponentInterface $component,
        Object $context
    ) {
        foreach ($this->getChildNodes($node) as $child) {
            $compiler->compile($child, $component, $context);
        }
    }

    /**
     * Get child nodes
     *
     * @param \DOMElement $node
     * @return \DOMElement[]
     */
    protected function getChildNodes(\DOMElement $node)
    {
        $childNodes = [];
        foreach ($node->childNodes as $child) {
            $childNodes[] = $child;
        }

        return $childNodes;
    }
}
