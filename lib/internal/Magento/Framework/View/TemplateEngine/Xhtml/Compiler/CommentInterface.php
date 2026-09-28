<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Interface CommentInterface
 *
 * @api
 */
interface CommentInterface
{
    /**
     * Compiles the Comment node
     *
     * @param \DOMComment $node
     * @param DataObject $processedObject
     * @return void
     */
    public function compile(\DOMComment $node, DataObject $processedObject);
}
