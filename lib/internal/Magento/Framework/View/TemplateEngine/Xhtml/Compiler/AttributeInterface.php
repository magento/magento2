<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\Object;

/**
 * Interface AttributeInterface
 */
interface AttributeInterface
{
    /**
     * Compiles the Element node
     *
     * @param \DOMAttr $node
     * @param Object $processedObject
     * @return void
     */
    public function compile(\DOMAttr $node, Object $processedObject);
}
