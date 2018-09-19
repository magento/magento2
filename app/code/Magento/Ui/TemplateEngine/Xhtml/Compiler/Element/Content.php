<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler\Element;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Element\ElementInterface;

/**
 * Class Content
 */
class Content implements ElementInterface
{
    /**
     * Compiles the Element node
     *
     * @param CompilerInterface $compiler
     * @param \DOMElement $node
     * @param DataObject $processedObject
     * @param DataObject $context
     * @return void
     */
    public function compile(
        CompilerInterface $compiler,
        \DOMElement $node,
        DataObject $processedObject,
        DataObject $context
    ) {
        $name = $node->getAttribute('name');
        /** @var UiComponentInterface $processedObject */
        $content = (string)$processedObject->renderChildComponent($name);
        $name .= '_' . sprintf('%x', crc32(spl_object_hash($context)));
        if (!empty($content)) {
            $compiler->setPostprocessingData($name, $content);
            $newNode = $node->ownerDocument->createTextNode(
                CompilerInterface::PATTERN_TAG . $name . CompilerInterface::PATTERN_TAG
            );
            $node->parentNode->replaceChild($newNode, $node);
        } else {
            $node->parentNode->removeChild($node);
        }
    }
}
