<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Helper;

use Magento\Bundle\Model\ResourceModel\Option\Collection;

/**
 * Test helper for Magento\Bundle\Model\ResourceModel\Option\Collection
 *
 * Extends Collection to add custom methods for testing.
 * Overrides methods that require database connection to work in unit test environment.
 */
class CollectionTestHelper extends Collection
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
     * Override setConnection to handle null values in test environment
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $conn
     * @return self
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setConnection($conn): self
    {
        // In unit test environment, we don't need an actual connection
        // Just return self to maintain fluent interface
        return $this;
    }

    /**
     * Override _resetState to prevent issues with null connection
     *
     * @return void
     */
    public function _resetState(): void
    {
        // Skip parent _resetState which calls setConnection with potentially null _conn
        // In unit tests, we don't need to reset database-related state
    }

    /**
     * Get resource collection for testing
     *
     * @return mixed
     */
    public function getResourceCollection()
    {
        return $this->data['resource_collection'] ?? null;
    }
}
