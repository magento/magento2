<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Interface TextInterface
 * @since 2.0.0
 */
interface TextInterface
{
    /**
     * Compiles the Element node
     *
     * @param \DOMText $node
     * @param DataObject $processedObject
     * @return void
     * @since 2.0.0
     */
    public function compile(\DOMText $node, DataObject $processedObject);
}
