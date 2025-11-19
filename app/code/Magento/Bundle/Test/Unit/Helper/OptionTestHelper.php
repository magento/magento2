<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Helper;

use Magento\Bundle\Model\Option;

/**
 * Test helper for Magento\Bundle\Model\Option
 *
 * Extends Magento\Bundle\Model\Option to add custom methods for testing.
 * All standard OptionInterface methods are inherited from parent class.
 */
class OptionTestHelper extends Option
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Constructor - skip parent to avoid dependencies
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependency injection issues
    }

    /**
     * Custom setDefaultTitle method for testing
     *
     * This method doesn't exist in parent Option class.
     *
     * @param mixed $title
     * @return self
     */
    public function setDefaultTitle($title): self
    {
        $this->data['default_title'] = $title;
        return $this;
    }

    /**
     * Custom getDefaultTitle method for testing
     *
     * This method doesn't exist in parent Option class.
     *
     * @return mixed
     */
    public function getDefaultTitle()
    {
        return $this->data['default_title'] ?? null;
    }

    /**
     * Set test data for flexible state management
     *
     * This method doesn't exist in parent Option class.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setTestData(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get test data
     *
     * This method doesn't exist in parent Option class.
     *
     * @param string $key
     * @return mixed
     */
    public function getTestData(string $key)
    {
        return $this->data[$key] ?? null;
    }
}
