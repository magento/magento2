<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Helper;

use Magento\Checkout\Model\Session;

/**
 * Test helper for Checkout Session
 *
 * Adds custom methods for testing downloadable products functionality.
 * Follows the migration rule: only add custom methods that don't exist in parent.
 */
class CheckoutSessionTestHelper extends Session
{
    /**
     * @var bool
     */
    private $hasDownloadableProducts = false;

    /** @var array */
    private array $testData = [];

    /**
     * Skip parent constructor to avoid dependencies
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependency injection issues
    }

    /**
     * Get has downloadable products flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHasDownloadableProducts(): bool
    {
        return $this->hasDownloadableProducts;
    }

    /**
     * Set has downloadable products flag
     *
     * @param bool $hasDownloadableProducts
     * @return self
     */
    public function setHasDownloadableProducts($hasDownloadableProducts): self
    {
        $this->hasDownloadableProducts = $hasDownloadableProducts;
        return $this;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setLastQuoteId($id)
    {
        $this->testData['last_quote_id'] = $id;
        return $this;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setLastSuccessQuoteId($id)
    {
        $this->testData['last_success_quote_id'] = $id;
        return $this;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setLastOrderId($id)
    {
        $this->testData['last_order_id'] = $id;
        return $this;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setLastRealOrderId($id)
    {
        $this->testData['last_real_order_id'] = $id;
        return $this;
    }

    /**
     * @param mixed $status
     * @return $this
     */
    public function setLastOrderStatus($status)
    {
        $this->testData['last_order_status'] = $status;
        return $this;
    }
}
