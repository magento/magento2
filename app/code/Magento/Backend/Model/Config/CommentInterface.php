<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System configuration comment model interface
 */
namespace Magento\Backend\Model\Config;

interface CommentInterface
{
    /**
     * Retrieve element comment by element value
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue);
}
