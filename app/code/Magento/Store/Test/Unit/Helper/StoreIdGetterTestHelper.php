<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Helper;

/**
 * Lightweight helper exposing getStoreId() for tests needing a store object.
 */
class StoreIdGetterTestHelper
{
    /** @var int */
    private $storeId;

    /**
     * @param int $storeId
     */
    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Return provided store id.
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }
}
