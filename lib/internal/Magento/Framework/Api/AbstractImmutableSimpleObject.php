<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

/**
 * Base Class for simple immutable data Objects
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractImmutableSimpleObject
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Initialize internal storage
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Retrieves a value from the data array if set, or null otherwise.
     *
     * @param string $key
     * @return mixed|null
     */
    protected function get($key)
    {
        return $this->data[$key] ?? null;
    }
}
