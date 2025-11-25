<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Helper;

use Magento\Backend\Block\Widget\Button;

/**
 * Test helper for Button class with custom methods
 */
class ButtonTestHelper extends Button
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Skip parent constructor to avoid dependencies
     */
    public function __construct()
    {
        // Skip parent constructor - clean initialization
        $this->data = [];
    }

    /**
     * Custom isAllowed method for testing
     *
     * @param string|null $resource
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isAllowed($resource = null): bool
    {
        return $this->data['is_allowed'] ?? true;
    }

    /**
     * Set is allowed for testing
     *
     * @param bool $isAllowed
     * @return self
     */
    public function setIsAllowed(bool $isAllowed): self
    {
        $this->data['is_allowed'] = $isAllowed;
        return $this;
    }

    /**
     * Override getAuthorization method
     *
     * @return mixed
     */
    public function getAuthorization()
    {
        return $this->data['authorization'] ?? null;
    }

    /**
     * Set authorization for testing
     *
     * @param mixed $authorization
     * @return self
     */
    public function setAuthorization($authorization): self
    {
        $this->data['authorization'] = $authorization;
        return $this;
    }

    /**
     * Override toHtml method
     *
     * @return string
     */
    public function toHtml(): string
    {
        return $this->data['html'] ?? '';
    }

    /**
     * Set HTML output for testing
     *
     * @param string $html
     * @return self
     */
    public function setHtml(string $html): self
    {
        $this->data['html'] = $html;
        return $this;
    }
}
