<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl;

/**
 * Factory for @see Schema
 */
class SchemaFactory
{
    /**
     * Create a Schema class
     *
     * @param array $config
     * @return Schema
     */
    public function create(array $config) : Schema
    {
        return new Schema($config);
    }
}
