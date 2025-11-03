<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\ProductOption;

/**
 * Test helper for Magento\Quote\Model\Quote\ProductOption
 *
 * Extends the concrete ProductOption class to add custom methods for testing
 */
class ProductOptionTestHelper extends ProductOption
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
     * Get configurable item options for testing
     * This method is used by ConfigurableProduct tests
     *
     * @return array
     */
    public function getConfigurableItemOptions()
    {
        return $this->data['configurable_item_options'] ?? [];
    }

    /**
     * Set configurable item options for testing
     * This method is used by ConfigurableProduct tests
     *
     * @param array $options
     * @return self
     */
    public function setConfigurableItemOptions($options): self
    {
        $this->data['configurable_item_options'] = $options;
        return $this;
    }

    /**
     * Override getExtensionAttributes to work without constructor
     *
     * @return mixed
     */
    public function getExtensionAttributes()
    {
        return $this->data['extension_attributes'] ?? null;
    }

    /**
     * Override setExtensionAttributes to work without constructor
     *
     * @param mixed $extensionAttributes
     * @return self
     */
    public function setExtensionAttributes($extensionAttributes)
    {
        $this->data['extension_attributes'] = $extensionAttributes;
        return $this;
    }
}
