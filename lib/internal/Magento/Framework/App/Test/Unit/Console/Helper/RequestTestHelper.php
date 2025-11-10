<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Console\Helper;

use Magento\Framework\App\Console\Request;

/**
 * Test helper for Console Request with additional methods for testing.
 *
 * Extends Console\Request to provide methods which don't exist in the base class.
 * PHPUnit 12 removed addMethods() support, so this helper is necessary for unit tests.
 */
class RequestTestHelper extends Request
{
    /**
     * @var bool
     */
    private $isPost = false;

    /**
     * Constructor.
     *
     * Skip parent constructor to avoid dependencies.
     */
    public function __construct()
    {
        $this->params = [];
    }

    /**
     * Get header value.
     *
     * Mock implementation for testing purposes. Returns empty string to avoid wishlist logic.
     *
     * @param string $name
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getHeader($name)
    {
        return '';
    }

    /**
     * Check if request is POST.
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->isPost;
    }

    /**
     * Set POST flag.
     *
     * @param bool $isPost
     * @return $this
     */
    public function setPost(bool $isPost): self
    {
        $this->isPost = $isPost;
        return $this;
    }

    /**
     * Set single parameter.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setParam(string $key, $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }
}
