<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote;

/**
 * Test helper for a controllable Quote instance with fixtures for totals, items, and store.
 */
class QuoteFixtureTestHelper extends Quote
{
    /** @var array|null */
    private $totals;

    /** @var array|null */
    private $items;

    /** @var mixed|null */
    private $store;

    public function __construct()
    {
    }

    /**
     * @param array $totals
     * @return $this
     */
    public function setFixtureTotals(array $totals)
    {
        $this->totals = $totals;
        return $this;
    }

    /**
     * @param array $items
     * @return $this
     */
    public function setFixtureItems(array $items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @param mixed $store
     * @return $this
     */
    public function setFixtureStore($store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getTotals()
    {
        return $this->totals;
    }

    /**
     * @return array|null
     */
    public function getAllVisibleItems()
    {
        return $this->items;
    }

    /**
     * @return mixed|null
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @return bool
     */
    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHasError()
    {
        return false;
    }
}
