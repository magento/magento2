<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

/**
 * GraphQL schema generator interface.
 */
interface SchemaGeneratorInterface
{
    /**
     * Generate GraphQL schema.
     *
     * @return \GraphQL\Type\Schema
     */
    public function generate();
}
