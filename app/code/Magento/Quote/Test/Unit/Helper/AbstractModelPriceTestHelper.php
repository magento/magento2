<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Framework\Model\AbstractModel;

/**
 * Helper model to expose getPrice() for testing UpdateQuoteItems plugin.
 */
class AbstractModelPriceTestHelper extends AbstractModel
{
    /** @var float|int|null */
    private $price;

    public function __construct()
    {
        // Intentionally skip parent constructor
    }

    /**
     * Return test price value.
     *
     * @return float|int|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Allow tests to set price value when using a real instance instead of a mock.
     *
     * @param float|int|null $price
     * @return $this
     */
    public function setTestPrice($price)
    {
        $this->price = $price;
        return $this;
    }
}
