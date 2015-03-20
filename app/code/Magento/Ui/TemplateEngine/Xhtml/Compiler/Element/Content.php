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
 * Class Content
 */
class Content implements ElementInterface
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
        $name = $node->getAttribute('name');
        $content = (string)$component->renderChildComponent($name);
        $name .= '_' . sprintf('%x', crc32(spl_object_hash($context)));
        if (!empty($content)) {
            $compiler->setPostprocessingData($name, $content);
            $newNode = $node->ownerDocument->createTextNode(
                Compiler::PATTERN_TAG . $name . Compiler::PATTERN_TAG
            );
            $node->parentNode->replaceChild($newNode, $node);
        } else {
            $node->parentNode->removeChild($node);
        }
    }
}
