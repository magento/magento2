<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Helper;

use Magento\Eav\Model\Entity\Attribute;

/**
 * Test helper class for EAV Attribute with custom methods
 *
 * This helper extends the EAV Attribute class to provide custom methods
 * needed for testing that don't exist in the parent class.
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class AttributeTestHelper extends Attribute
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
    }

    /**
     * Override getId to work without constructor
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->data['id'] ?? $this->data['attribute_id'] ?? null;
    }

    /**
     * Override getData to work with our data array
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

        if ($index !== null) {
            return $this->data[$key][$index] ?? null;
        }

        return $this->data[$key] ?? null;
    }

    /**
     * Override setData to work with our data array
     *
     * @param string|array $key
     * @param mixed $value
     * @return self
     */
    public function setData($key, $value = null): self
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * Get attribute name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    /**
     * Set attribute name
     *
     * @param mixed $name
     * @return self
     */
    public function setName($name): self
    {
        $this->data['name'] = $name;
        return $this;
    }

    /**
     * Mock method for testing
     *
     * @param mixed $setId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isInSet($setId)
    {
        return $this->data['is_in_set'] ?? false;
    }

    /**
     * Mock method for testing
     *
     * @return self
     */
    public function save(): self
    {
        return $this;
    }

    /**
     * Mock method for testing
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->data['attribute_code'] ?? 'some_code';
    }

    /**
     * Get store label for testing
     *
     * @param int|null $storeId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getStoreLabel($storeId = null)
    {
        return $this->data['store_label'] ?? $this->getFrontendLabel();
    }

    /**
     * Get source for testing
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->data['source'] ?? null;
    }
}
