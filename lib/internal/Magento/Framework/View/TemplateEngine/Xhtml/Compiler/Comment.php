<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Class Comment
 */
class Comment implements CommentInterface
{
    /**
     * Compiles the Comment node
     *
     * @param \DOMComment $node
     * @param DataObject $processedObject
     * @return void
     */
    public function compile(\DOMComment $node, DataObject $processedObject)
    {
        //
    }
}
