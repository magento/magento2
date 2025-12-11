<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

class ProductExtensionForSubtotalTestHelper
{
    /** @var mixed */
    private $stockItem;

    public function __construct($stockItem = null)
    {
        $this->stockItem = $stockItem;
    }

    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function setStockItem($stockItem)
    {
        $this->stockItem = $stockItem;
        return $this;
    }

    public function __call($name, $arguments)
    {
        // Other extension attributes are not needed for this test
        return null;
    }
}
