<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Event\Test\Unit\Helper;

use Magento\Framework\Event;

/**
 * Test helper for Event class
 */
class EventTestHelper extends Event
{
    /**
     * @var array
     */
    private $data = [];

    public function __construct()
    {
        // Skip parent constructor to avoid dependencies
    }

    /**
     * Get product (custom method for testing)
     *
     * @return mixed
     */
    public function getProduct()
    {
        return $this->data['product'] ?? null;
    }

    /**
     * Set product (custom method for testing)
     *
     * @param mixed $product
     * @return $this
     */
    public function setProduct($product): self
    {
        $this->data['product'] = $product;
        return $this;
    }

    /**
     * Get collection (custom method for testing)
     *
     * @return mixed
     */
    public function getCollection()
    {
        return $this->data['collection'] ?? null;
    }

    /**
     * Set collection (custom method for testing)
     *
     * @param mixed $collection
     * @return $this
     */
    public function setCollection($collection): self
    {
        $this->data['collection'] = $collection;
        return $this;
    }

    /**
     * Get limit (custom method for testing)
     *
     * @return mixed
     */
    public function getLimit()
    {
        return $this->data['limit'] ?? null;
    }

    /**
     * Set limit (custom method for testing)
     *
     * @param mixed $limit
     * @return $this
     */
    public function setLimit($limit): self
    {
        $this->data['limit'] = $limit;
        return $this;
    }

    /**
     * Get items (custom method for testing)
     *
     * @return mixed
     */
    public function getItems()
    {
        return $this->data['items'] ?? null;
    }

    /**
     * Set items (custom method for testing)
     *
     * @param mixed $items
     * @return $this
     */
    public function setItems($items): self
    {
        $this->data['items'] = $items;
        return $this;
    }

    /**
     * Get store (custom method for testing)
     *
     * @return mixed
     */
    public function getStore()
    {
        return $this->data['store'] ?? null;
    }

    /**
     * Get result (custom method for testing)
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->data['result'] ?? null;
    }

    /**
     * Get quote (custom method for testing)
     *
     * @return mixed
     */
    public function getQuote()
    {
        return $this->data['quote'] ?? null;
    }

    /**
     * Get order (custom method for testing)
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->data['order'] ?? null;
    }
}
