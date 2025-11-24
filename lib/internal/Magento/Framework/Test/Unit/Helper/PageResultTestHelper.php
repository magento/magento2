<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Helper;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Test helper for Page Result
 *
 * This helper implements ResultInterface to provide test-specific functionality
 * for page responses with layout support.
 *
 * Production code calls: addHandle()->getLayout()->getBlock()
 * All three methods are required for the fluent interface chain.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageResultTestHelper implements ResultInterface
{
    /**
     * @var LayoutInterface|null
     */
    private $layout;

    /**
     * Add handle (fluent interface stub)
     *
     * @param string $handle
     * @return $this
     */
    public function addHandle($handle)
    {
        // Production code calls this in fluent chain
        // Handle is not stored as it's not used
        return $this;
    }

    /**
     * Get layout
     *
     * @return LayoutInterface|null
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set layout
     *
     * @param LayoutInterface $layout
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
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
