<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Element;

use Magento\Framework\DataObject;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;

/**
 * Interface ElementInterface
 * @since 2.0.0
 */
interface ElementInterface
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
    );
}
