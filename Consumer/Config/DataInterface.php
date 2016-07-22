<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

/**
 * Queue consumer config data interface.
 */
interface DataInterface
{
    /**
     * Get config data.
     *
     * @return array
     */
    public function get();
}
