<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Helper;

use Magento\Framework\DataObject;

/**
 * Test helper for BuyRequest DataObject
 *
 * This helper extends the DataObject class to provide test-specific functionality
 * for buy requests with custom data storage behavior.
 *
 * Key Features:
 * - Custom getData() that returns from isolated $data array (not parent's $_data)
 * - Conditional setData() behavior based on $useCustomSetData flag
 * - Custom unsetData() that operates on isolated $data array
 * - setCustomPrice() for direct custom_price manipulation
 *
 * Used by tests that need precise control over data storage and retrieval.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class BuyRequestDataObjectTestHelper extends DataObject
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool - whether to use custom setData that affects getData
     */
    private $useCustomSetData = false;

    /**
     * Constructor
     *
     * @param bool $useCustomSetData Whether setData should update the custom data array
     */
    public function __construct($useCustomSetData = false)
    {
        $this->useCustomSetData = $useCustomSetData;
        parent::__construct();
    }

    /**
     * Get data - only returns from custom data array
     *
     * @param string $key
     * @param mixed $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        if ($key === '') {
            return $this->data;
        }
        return $this->data[$key] ?? null;
    }

    /**
     * Set data - optionally updates custom data array based on mode
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if ($this->useCustomSetData) {
            if (is_array($key)) {
                $this->data = $key;
            } else {
                $this->data[$key] = $value;
            }
        }
        // Always call parent for compatibility
        return parent::setData($key, $value);
    }

    /**
     * Unset data from custom data array
     *
     * @param string|null $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if ($key === null) {
            $this->data = [];
        } elseif (is_string($key)) {
            unset($this->data[$key]);
        }
        // Also call parent to maintain compatibility
        parent::unsetData($key);
        return $this;
    }

    /**
     * Set custom price
     *
     * Custom method to set custom_price directly in the custom data array.
     * This is called directly by tests and cannot use the generic setData().
     *
     * @param float $price
     * @return $this
     */
    public function setCustomPrice($price)
    {
        $this->data['custom_price'] = $price;
        return $this;
    }
}
