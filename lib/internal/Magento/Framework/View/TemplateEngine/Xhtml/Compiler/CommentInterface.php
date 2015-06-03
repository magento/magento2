<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\Object;

/**
 * Interface CommentInterface
 */
interface CommentInterface
{
    /**
     * Compiles the Comment node
     *
     * @param \DOMComment $node
     * @param Object $processedObject
     * @return void
     */
    public function compile(\DOMComment $node, Object $processedObject);
}
