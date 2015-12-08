<?php
/**
 * Reader responsible for retrieving provided scope of configuration from storage
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Config reader interface.
 *
 * @api
 */
interface ReaderInterface
{
    /**
     * Read configuration scope
     *
     * @return array
     */
    public function read();
}
