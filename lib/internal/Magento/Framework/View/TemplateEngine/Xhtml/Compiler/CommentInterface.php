<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Interface CommentInterface
 * @since 2.0.0
 */
interface CommentInterface
{
    /**
     * Compiles the Comment node
     *
     * @param \DOMComment $node
     * @param DataObject $processedObject
     * @return void
     * @since 2.0.0
     */
    public function compile(\DOMComment $node, DataObject $processedObject);
}
