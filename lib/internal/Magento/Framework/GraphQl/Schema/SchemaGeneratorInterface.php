<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema;

use Magento\Framework\GraphQl\Schema;

/**
 * GraphQL schema generator interface.
 *
 * @api
 */
interface SchemaGeneratorInterface
{
    /**
     * Generate GraphQL schema.
     *
     * @return Schema
     */
    public function generate(): Schema;
}
