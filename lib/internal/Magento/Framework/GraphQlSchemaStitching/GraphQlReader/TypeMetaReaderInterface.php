<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader;

/**
 * Reads and returns metadata as array for a specific type if it finds an adequate implementation for that type
 */
interface TypeMetaReaderInterface
{
    /**
     * Read schema data from type metadata if proper type is provided for a specific implementation
     *
     * @param \GraphQL\Type\Definition\Type $typeMeta
     * @return array
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta)  : ?array;
}
