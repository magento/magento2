<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader;

interface TypeMetaReaderInterface
{
    /**
     * Read schema data from type metadata
     *
     * @param \GraphQL\Type\Definition\Type $typeMeta
     * @return array
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : ?array;
}
