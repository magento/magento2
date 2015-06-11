<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Element;

use Magento\Framework\Object;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;

/**
 * Interface ElementInterface
 */
interface ElementInterface
{
    /**
     * Compiles the Element node
     *
     * @param CompilerInterface $compiler
     * @param \DOMElement $node
     * @param Object $processedObject
     * @param Object $context
     * @return void
     */
    public function compile(CompilerInterface $compiler, \DOMElement $node, Object $processedObject, Object $context);
}
