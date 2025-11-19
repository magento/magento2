<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale\Test\Unit\Helper;

use Magento\Framework\Locale\Currency;

/**
 * Test helper for CurrencyInterface
 */
class CurrencyTestHelper extends Currency
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
        // No parent constructor to avoid dependencies
    }

    /**
     * @inheritdoc
     */
    public function getCurrency($currency = null)
    {
        return $this->data['currency'] ?? $this;
    }

    /**
     * Set currency
     *
     * @param mixed $currency
     * @return $this
     */
    public function setCurrency($currency): self
    {
        $this->data['currency'] = $currency;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toCurrency($value, $options = [])
    {
        return '$' . number_format((float)$value, 2);
    }
}
