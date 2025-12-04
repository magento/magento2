<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Form\Element\Test\Unit\Helper;

use Magento\Framework\Data\Form\Element\Select;

/**
 * Test helper for Magento\Framework\Data\Form\Element\Select
 *
 * Extends the concrete Select class to add custom methods for testing
 */
class SelectTestHelper extends Select
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Constructor - skip parent constructor to avoid dependencies
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependency injection issues
    }

    /**
     * Get values for testing
     *
     * @return array
     */
    public function getValues()
    {
        return $this->data['values'] ?? [];
    }

    /**
     * Set values for testing
     *
     * @param array $values
     * @return self
     */
    public function setValues($values): self
    {
        $this->data['values'] = $values;
        return $this;
    }
}
