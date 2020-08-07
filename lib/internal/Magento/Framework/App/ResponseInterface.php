<?php
/**
 * Application response
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * @api
 * @since 100.0.2
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
