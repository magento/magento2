<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Helper;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Test helper for JSON Result
 *
 * This helper implements ResultInterface to provide test-specific functionality
 * for JSON responses in controller tests.
 *
 * The production code calls setJsonData() for fluent interface, but doesn't use
 * the stored data, so we don't need to store it.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class JsonResultTestHelper implements ResultInterface
{
    /**
     * Set JSON data (fluent interface only - data not stored)
     *
     * @param array $data
     * @return $this
     */
    public function setJsonData($data)
    {
        // Production code calls this for fluent interface
        // No need to store data as it's never retrieved
        return $this;
    }

    /**
     * Set HTTP response code
     *
     * @param int $code
     * @return $this
     */
    public function setHttpResponseCode($code)
    {
        return $this;
    }

    /**
     * Set header
     *
     * @param string $name
     * @param string $value
     * @param bool|null $replace
     * @return $this
     */
    public function setHeader($name, $value, $replace = null)
    {
        return $this;
    }

    /**
     * Render result
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function renderResult(ResponseInterface $response)
    {
        return $response;
    }
}
