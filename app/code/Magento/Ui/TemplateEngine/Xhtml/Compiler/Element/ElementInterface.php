<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler\Element;

use Magento\Framework\Object;
use Magento\Ui\TemplateEngine\Xhtml\Compiler;
use Magento\Ui\TemplateEngine\Xhtml\CompilerInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ElementInterface
 */
interface ElementInterface
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
    );
}
