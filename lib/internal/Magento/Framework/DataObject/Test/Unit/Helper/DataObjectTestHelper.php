<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DataObject\Test\Unit\Helper;

use Magento\Framework\DataObject;

/**
 * Test helper for Magento\Framework\DataObject
 *
 * Extends the DataObject class to add custom methods for testing
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class DataObjectTestHelper extends DataObject
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
     * Custom getItems method for testing
     *
     * @return mixed
     */
    public function getItems()
    {
        return $this->data['items'] ?? [];
    }

    /**
     * Custom getOptionId method for testing
     *
     * @return mixed
     */
    public function getOptionId()
    {
        return $this->data['option_id'] ?? null;
    }

    /**
     * Custom getBundleOption method for testing
     *
     * @return mixed
     */
    public function getBundleOption()
    {
        return $this->data['bundle_option'] ?? null;
    }

    /**
     * Set bundle option for testing
     *
     * @param mixed $bundleOption
     * @return self
     */
    public function setBundleOption($bundleOption): self
    {
        $this->data['bundle_option'] = $bundleOption;
        return $this;
    }

    /**
     * Custom getBundleOptionQty method for testing
     *
     * @return mixed
     */
    public function getBundleOptionQty()
    {
        return $this->data['bundle_option_qty'] ?? [];
    }

    /**
     * Custom getOptions method for testing
     *
     * @return mixed
     */
    public function getOptions()
    {
        return $this->data['options'] ?? null;
    }

    /**
     * Custom getSuperProductConfig method for testing
     *
     * @return array
     */
    public function getSuperProductConfig(): array
    {
        return $this->data['super_product_config'] ?? [];
    }

    /**
     * Custom getQty method for testing
     *
     * @return mixed
     */
    public function getQty()
    {
        return $this->data['qty'] ?? null;
    }

    /**
     * Set qty for testing
     *
     * @param mixed $qty
     * @return self
     */
    public function setQty($qty): self
    {
        $this->data['qty'] = $qty;
        return $this;
    }

    /**
     * Custom getBundleOptionsData method for testing
     *
     * @return mixed
     */
    public function getBundleOptionsData()
    {
        return $this->data['bundle_options_data'] ?? null;
    }

    /**
     * Check if salable for testing (selection method)
     *
     * @return bool
     */
    public function isSalable(): bool
    {
        return $this->data['is_salable'] ?? false;
    }

    /**
     * Get selection can change qty for testing
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getSelectionCanChangeQty(): bool
    {
        return $this->data['selection_can_change_qty'] ?? false;
    }

    /**
     * Get selection ID for testing
     *
     * @return mixed
     */
    public function getSelectionId()
    {
        return $this->data['selection_id'] ?? null;
    }

    /**
     * Add custom option for testing
     *
     * @param mixed $option
     * @return self
     */
    public function addCustomOption($option): self
    {
        if (!isset($this->data['custom_options'])) {
            $this->data['custom_options'] = [];
        }
        $this->data['custom_options'][] = $option;
        return $this;
    }

    /**
     * Get type instance for testing
     *
     * @return mixed
     */
    public function getTypeInstance()
    {
        return $this->data['type_instance'] ?? null;
    }

    /**
     * Get option for testing
     *
     * @return mixed
     */
    public function getOption()
    {
        return $this->data['option'] ?? null;
    }

    /**
     * Override getId to work without constructor
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->data['id'] ?? null;
    }

    /**
     * Set ID for testing
     *
     * @param mixed $id
     * @return self
     */
    public function setId($id): self
    {
        $this->data['id'] = $id;
        return $this;
    }

    /**
     * Get SKU for testing
     *
     * @return mixed
     */
    public function getSku()
    {
        return $this->data['sku'] ?? null;
    }

    /**
     * Set SKU for testing
     *
     * @param mixed $sku
     * @return self
     */
    public function setSku($sku): self
    {
        $this->data['sku'] = $sku;
        return $this;
    }

    /**
     * Get entity ID for testing
     *
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->data['entity_id'] ?? null;
    }

    /**
     * Get weight for testing
     *
     * @return mixed
     */
    public function getWeight()
    {
        return $this->data['weight'] ?? null;
    }

    /**
     * Check if virtual for testing
     *
     * @return bool
     */
    public function isVirtual(): bool
    {
        return $this->data['is_virtual'] ?? false;
    }

    /**
     * Get required for testing
     *
     * @return mixed
     */
    public function getRequired()
    {
        return $this->data['required'] ?? null;
    }

    /**
     * Custom setIsAllowed method for testing
     *
     * @param bool $allowed
     * @return self
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setIsAllowed($allowed): self
    {
        return $this;
    }

    /**
     * Custom getLinks method for testing
     *
     * @return mixed
     */
    public function getLinks()
    {
        return $this->data['links'];
    }

    /**
     * Get position method for testing
     *
     * @return mixed
     */
    public function getPosition()
    {
        return $this->data['position'] ?? null;
    }

    /**
     * Set position method for testing
     *
     * @param mixed $position
     * @return self
     */
    public function setPosition($position): self
    {
        $this->data['position'] = $position;
        return $this;
    }

    /**
     * Get product attribute for testing
     *
     * @return mixed
     */
    public function getProductAttribute()
    {
        return $this->data['product_attribute'] ?? null;
    }

    /**
     * Set product attribute for testing
     *
     * @param mixed $productAttribute
     * @return self
     */
    public function setProductAttribute($productAttribute): self
    {
        $this->data['product_attribute'] = $productAttribute;
        return $this;
    }

    /**
     * Get name for testing
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->data['name'] ?? null;
    }

    /**
     * Set name for testing
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
     * Get price for testing
     *
     * @return mixed
     */
    public function getPrice()
    {
        return $this->data['price'] ?? null;
    }

    /**
     * Set price for testing
     *
     * @param mixed $price
     * @return self
     */
    public function setPrice($price): self
    {
        $this->data['price'] = $price;
        return $this;
    }
}
