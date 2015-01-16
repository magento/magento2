<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * HTTP response implementation that is used instead core one for testing
 */
namespace Magento\TestFramework;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Response extends \Magento\Framework\App\Response\Http
{
    /**
     * @inherit
     */
    public $headersSentThrowsException = false;

    /**
     * Prevent generating exceptions if headers are already sent
     *
     * Prevents throwing an exception in \Zend_Controller_Response_Abstract::canSendHeaders()
     * All functionality that depend on headers validation should be covered with unit tests by mocking response.
     *
     * @param bool $throw
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canSendHeaders($throw = false)
    {
        return true;
    }

    public function sendResponse()
    {
    }
}
