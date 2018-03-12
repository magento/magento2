<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Type;

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
    public function create($config) : Schema
    {
        return new Schema($config);
    }
}
