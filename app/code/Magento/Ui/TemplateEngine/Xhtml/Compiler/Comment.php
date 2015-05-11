<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Comment
 */
class Comment implements CommentInterface
{
    /**
     * Compiles the Comment node
     *
     * @param \DOMComment $node
     * @param UiComponentInterface $component
     * @return void
     */
    public function compile(\DOMComment $node, UiComponentInterface $component)
    {
        //
    }
}
