<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Helper;

use Magento\Store\Model\Store;

/**
 * Test helper for Store that exposes a static website id.
 */
class StoreWebsiteIdTestHelper extends Store
{
    /** @var int */
    private $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->id;
    }
}
