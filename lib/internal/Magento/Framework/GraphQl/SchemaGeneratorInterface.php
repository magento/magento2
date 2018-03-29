<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl;

use Magento\Framework\GraphQl\Type\Schema;

/**
 * GraphQL schema generator interface.
 */
interface SchemaGeneratorInterface
{
    /**
     * Generate GraphQL schema.
     *
     * @return Schema
     */
    public function generate() : Schema;
}
