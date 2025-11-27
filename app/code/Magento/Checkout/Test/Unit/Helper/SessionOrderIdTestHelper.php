<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Helper;

use Magento\Checkout\Model\Session;

/**
 * Test helper that exposes setters/getters for last order id on Checkout session.
 */
class SessionOrderIdTestHelper extends Session
{
    /** @var int|string|null */
    private $lastOrderId;

    /**
     * Override parent constructor; not needed for tests.
     */
    public function __construct()
    {
    }

    /**
     * @param int|string|null $id
     * @return $this
     */
    public function setLastOrderId($id)
    {
        $this->lastOrderId = $id;
        return $this;
    }

    /**
     * @return int|string|null
     */
    public function getLastOrderId()
    {
        return $this->lastOrderId;
    }
}
