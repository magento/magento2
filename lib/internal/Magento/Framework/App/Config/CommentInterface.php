<?php
/**
 * Provide access to data. Each Source can be responsible for each storage, where config data can be placed
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
