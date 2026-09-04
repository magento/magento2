<?php
/**
 * Provide access to data. Each Source can be responsible for each storage, where config data can be placed
 *
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

/**
 * Interface CommentInterface
 *
 * @api
 */
interface CommentInterface
{
    /**
     * Retrieve comment for configuration data.
     *
     * @return string
     */
    public function get();
}
