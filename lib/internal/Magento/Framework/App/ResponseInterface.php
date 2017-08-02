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
 * @since 2.0.0
 */
interface ResponseInterface
{
    /**
     * Send response to client
     *
     * @return int|void
     * @since 2.0.0
     */
    public function sendResponse();
}
