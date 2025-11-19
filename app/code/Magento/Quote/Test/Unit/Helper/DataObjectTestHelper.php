<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Framework\DataObject;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Helper that extends DataObject for tests that need a product accessor.
 */
class DataObjectTestHelper extends DataObject implements ContextExtensionInterface
{
    /**
     * Get product from internal data storage for tests.
     *
     * @return mixed
     */
    public function getProduct()
    {
        return $this->getData('product');
    }

    /**
     * Set product reference for tests.
     *
     * @param mixed $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->setData('product', $product);
        return $this;
    }

    /**
     * Get reset count value from internal data.
     *
     * @return mixed
     */
    public function getResetCount()
    {
        return $this->getData('reset_count');
    }

    /**
     * Set reset count value for tests.
     *
     * @param mixed $value
     * @return $this
     */
    public function setResetCount($value)
    {
        $this->setData('reset_count', $value);
        return $this;
    }

    /**
     * Get custom price value from internal data.
     *
     * @return mixed
     */
    public function getCustomPrice()
    {
        return $this->getData('custom_price');
    }

    /**
     * Set custom price value for tests.
     *
     * @param mixed $value
     * @return $this
     */
    public function setCustomPrice($value)
    {
        $this->setData('custom_price', $value);
        return $this;
    }

    /**
     * Set value string for tests.
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->setData('value', $value);
        return $this;
    }

    /**
     * Set code string for tests.
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->setData('code', $code);
        return $this;
    }

    /**
     * Get id value from internal data to emulate request id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * Set id value for tests.
     *
     * @param mixed $value
     * @return $this
     */
    public function setId($value)
    {
        $this->setData('id', $value);
        return $this;
    }

    /**
     * Get store for tests (GraphQL context extension usage).
     *
     * @return mixed
     */
    public function getStore()
    {
        return $this->getData('store');
    }

    /**
     * Set store for tests.
     *
     * @param StoreInterface $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->setData('store', $store);
        return $this;
    }

    /**
     * Get is customer flag for tests.
     */
    public function getIsCustomer()
    {
        return (bool)$this->getData('is_customer');
    }

    /**
     * Set is customer flag for tests.
     */
    public function setIsCustomer($flag)
    {
        $this->setData('is_customer', $flag);
        return $this;
    }

    /**
     * Get sales channel for tests.
     */
    public function getSalesChannel()
    {
        return $this->getData('sales_channel');
    }

    /**
     * Set sales channel for tests.
     */
    public function setSalesChannel($salesChannel)
    {
        $this->setData('sales_channel', $salesChannel);
        return $this;
    }

    /**
     * Get customer group id for tests.
     */
    public function getCustomerGroupId()
    {
        return $this->getData('customer_group_id');
    }

    /**
     * Set customer group id for tests.
     */
    public function setCustomerGroupId($id)
    {
        $this->setData('customer_group_id', $id);
        return $this;
    }
}
