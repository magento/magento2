<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Helper;

use Magento\Bundle\Model\Selection;

/**
 * Test helper for Magento\Bundle\Model\Selection
 *
 * Extends Selection to add custom methods for testing
 */
class SelectionTestHelper extends Selection
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependencies
    }

    /**
     * Check if selection is salable for testing
     *
     * @return bool
     */
    public function isSalable(): bool
    {
        return $this->data['is_salable'] ?? false;
    }

    /**
     * Set is salable for testing
     *
     * @param bool $isSalable
     * @return self
     */
    public function setIsSalable(bool $isSalable): self
    {
        $this->data['is_salable'] = $isSalable;
        return $this;
    }
}
