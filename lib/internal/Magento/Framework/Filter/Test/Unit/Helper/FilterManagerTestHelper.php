<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit\Helper;

use Magento\Framework\Filter\FilterManager;

/**
 * Test helper class for FilterManager with custom methods
 *
 * This helper is placed in Magento_Framework as it's the core module
 * that contains the FilterManager class and is used by many other modules.
 */
class FilterManagerTestHelper extends FilterManager
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
        // Skip parent constructor
    }

    /**
     * Custom stripTags method for testing
     *
     * @param string $value
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function stripTags($value = '')
    {
        return $this->data['stripTags'] ?? 'Test';
    }

    /**
     * Custom sprintf method for testing
     *
     * @param string $format
     * @param mixed ...$args
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function sprintf($format, ...$args)
    {
        return $this->data['sprintf'] ?? $format;
    }

    /**
     * Set return value for stripTags
     *
     * @param string $value
     * @return self
     */
    public function setStripTagsReturn(string $value): self
    {
        $this->data['stripTags'] = $value;
        return $this;
    }

    /**
     * Set return value for sprintf
     *
     * @param string $value
     * @return self
     */
    public function setSprintfReturn(string $value): self
    {
        $this->data['sprintf'] = $value;
        return $this;
    }
}
