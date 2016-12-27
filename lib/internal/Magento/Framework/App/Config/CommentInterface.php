<?php
/**
 * Provide access to data. Each Source can be responsible for each storage, where config data can be placed
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Interface CommentInterface
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
