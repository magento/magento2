<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Interface TextInterface
 */
interface TextInterface
{
    /**
     * Compiles the Element node
     *
     * @param \DOMText $node
     * @param DataObject $processedObject
     * @return void
     */
    public function compile(\DOMText $node, DataObject $processedObject);
}
