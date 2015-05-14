<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler\Element;

use Magento\Framework\Object;
use Magento\Ui\TemplateEngine\Xhtml\Compiler;
use Magento\Ui\TemplateEngine\Xhtml\Result;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Render
 */
class Render implements ElementInterface
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
        $result = $component->renderChildComponent($node->getAttribute('name'));
        if ($result instanceof Result) {
            $node->parentNode->replaceChild($result->getDocumentElement(), $node);
        } else if (!empty($result) && is_scalar($result)) {
            $newFragment = $node->ownerDocument->createDocumentFragment();
            $newFragment->appendXML($result);
            $node->parentNode->replaceChild($newFragment, $node);
            $node->parentNode->removeChild($node);
        } else {
            $node->parentNode->removeChild($node);
        }
    }
}
