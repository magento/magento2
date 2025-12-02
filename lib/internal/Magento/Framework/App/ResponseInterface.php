<?php
/**
 * Application response
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
