<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader;

/**
 * Reads and returns metadata as array for a specific type if it finds an adequate implementation for that type
 *
 * @api
 */
interface TypeMetaReaderInterface
{
    /**
     * Read schema data from type metadata if proper type is provided for a specific implementation
     *
     * @param \GraphQL\Type\Definition\Type $typeMeta
     * @return array|null
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta): array;
}
