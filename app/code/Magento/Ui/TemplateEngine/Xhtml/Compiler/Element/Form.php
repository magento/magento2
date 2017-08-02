<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler\Element;

use Magento\Framework\DataObject;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Element\ElementInterface;

/**
 * Class Form
 * @since 2.0.0
 */
class Form implements ElementInterface
{
    /**
     * Compiles the Element node
     *
     * @param CompilerInterface $compiler
     * @param \DOMElement $node
     * @param DataObject $processedObject
     * @param DataObject $context
     * @return void
     * @since 2.0.0
     */
    public function compile(
        CompilerInterface $compiler,
        \DOMElement $node,
        DataObject $processedObject,
        DataObject $context
    ) {
        foreach ($this->getChildNodes($node) as $child) {
            $compiler->compile($child, $processedObject, $context);
        }
    }

    /**
     * Get child nodes
     *
     * @param \DOMElement $node
     * @return \DOMElement[]
     * @since 2.0.0
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
