<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Interface AttributeInterface
 */
interface AttributeInterface
{
    /**
     * Compiles the Element node
     *
     * @param \DOMAttr $node
     * @param DataObject $processedObject
     * @return void
     */
    public function compile(\DOMAttr $node, DataObject $processedObject);
}
