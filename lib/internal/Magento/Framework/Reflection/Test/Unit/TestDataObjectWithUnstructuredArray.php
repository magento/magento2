<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

class TestDataObjectWithUnstructuredArray
{
    /**
     * @var array
     */
    private $items;

    /**
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->items = $items;
    }

    /**
     * Get items
     *
     * @return UnstructuredArray
     */
    public function getItems()
    {
        return $this->items;
    }
}
