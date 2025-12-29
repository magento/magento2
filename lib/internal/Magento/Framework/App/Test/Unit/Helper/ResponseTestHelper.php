<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Helper;

use Magento\Framework\HTTP\PhpEnvironment\Response;

/**
 * Test helper for Magento\Framework\App\ResponseInterface
 */
class ResponseTestHelper extends Response
{
    public function __construct()
    {
    }

    /**
     * Send response
     *
     * @return $this
     */
    public function sendResponse()
    {
        return $this;
    }

    /**
     * Set HTTP response code (custom method for testing)
     *
     * @param int $code
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setHttpResponseCode($code)
    {
        return $this;
    }

    /**
     * Set header (custom method for testing)
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setHeader($name, $value, $replace = false)
    {
        return $this;
    }
}
