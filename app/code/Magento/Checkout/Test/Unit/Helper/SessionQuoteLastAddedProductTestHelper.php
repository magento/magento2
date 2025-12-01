<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Helper;

use Magento\Checkout\Model\Session;

/**
 * Test helper providing quote and lastAddedProductId accessors on Checkout session.
 */
class SessionQuoteLastAddedProductTestHelper extends Session
{
    /** @var mixed */
    private $quote;

    /** @var int|string|null */
    private $lastAddedProductId;

    /**
     * Override parent constructor; not needed for unit tests.
     */
    public function __construct()
    {
    }

    /**
     * @param mixed $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param int|string|null $id
     * @return $this
     */
    public function setLastAddedProductId($id)
    {
        $this->lastAddedProductId = $id;
        return $this;
    }

    /**
     * @return int|string|null
     */
    public function getLastAddedProductId()
    {
        return $this->lastAddedProductId;
    }
}
