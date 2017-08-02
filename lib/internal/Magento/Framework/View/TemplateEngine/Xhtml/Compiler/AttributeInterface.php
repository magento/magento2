<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Interface AttributeInterface
 * @since 2.0.0
 */
interface AttributeInterface
{
    /**
     * Compiles the Element node
     *
     * @param \DOMAttr $node
     * @param DataObject $processedObject
     * @return void
     * @since 2.0.0
     */
    public function compile(\DOMAttr $node, DataObject $processedObject);
}
