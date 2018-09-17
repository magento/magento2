<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler\Element;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\ResultInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Element\ElementInterface;

/**
 * Class Render
 */
class Render implements ElementInterface
{
    /**
     * Compiles the Element node
     *
     * @param CompilerInterface $compiler
     * @param \DOMElement $node
     * @param DataObject $processedObject
     * @param DataObject $context
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function compile(
        CompilerInterface $compiler,
        \DOMElement $node,
        DataObject $processedObject,
        DataObject $context
    ) {
        /** @var UiComponentInterface $processedObject */
        $result = $processedObject->renderChildComponent($node->getAttribute('name'));
        if ($result instanceof ResultInterface) {
            $node->parentNode->replaceChild($result->getDocumentElement(), $node);
        } else if (!empty($result) && is_scalar($result)) {
            $newFragment = $node->ownerDocument->createDocumentFragment();
            $newFragment->appendXML($result);
            $node->parentNode->replaceChild($newFragment, $node);
        } else {
            $node->parentNode->removeChild($node);
        }
    }
}
