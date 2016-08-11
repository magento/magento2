<?php
/**
 * Application response
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * @api
 */
interface ResponseInterface
{
    /**
     * Send response to client
     *
     * @return int|void
     */
    public function sendResponse();
}
