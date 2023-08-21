<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ResourceConnection;

/**
 *
 * Check if the fields belongs to an entity
 */
class EntityFieldChecker
{
    /***
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var Type
     */
    private Type $eavEntityType;

    /**
     * @param ResourceConnection $resource
     * @param Type $eavEntityType
     */
    public function __construct(ResourceConnection $resource, Type $eavEntityType)
    {
        $this->resource = $resource;
        $this->eavEntityType = $eavEntityType;
    }

    /**
     * Check if the field exists on the entity
     *
     * @param string $entityTypeCode
     * @param string $field
     * @return bool
     */
    public function fieldBelongToEntity(string $entityTypeCode, string $field): bool
    {
        $connection = $this->resource->getConnection();
        $columns = $connection->describeTable(
            $this->eavEntityType->loadByCode($entityTypeCode)->getAdditionalAttributeTable()
        );

        return array_key_exists($field, $columns);
    }
}
