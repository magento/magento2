<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * System configuration comment model interface
 */
namespace Magento\Config\Model\Config;

/**
 * @api
 * @since 100.0.2
 */
interface CommentInterface
{
    /**
     * Retrieve element comment by element value
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue);
}
