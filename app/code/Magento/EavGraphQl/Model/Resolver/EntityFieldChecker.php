<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\LocalizedException;

/**
 * Check if the fields belongs to an entity
 */
class EntityFieldChecker
{
    /**
     * Entity tables cache
     *
     * @var array
     */

    private array $entityTables = [];

    /***
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var EavConfig
     */
    private EavConfig $eavConfig;

    /**
     * @param ResourceConnection $resource
     * @param EavConfig $eavConfig
     */
    public function __construct(
        ResourceConnection $resource,
        EavConfig $eavConfig
    ) {
        $this->resource = $resource;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Check if the field exists on the entity
     *
     * @param string $entityTypeCode
     * @param string $field
     * @return bool
     * @throws LocalizedException
     */
    public function fieldBelongToEntity(string $entityTypeCode, string $field): bool
    {
        if (isset($this->entityTables[$entityTypeCode])) {
            $table = $this->entityTables[$entityTypeCode];
        } else {
            $table = $this->eavConfig->getEntityType($entityTypeCode)->getAdditionalAttributeTable();
            $this->entityTables[$entityTypeCode] = $table;
        }
        $connection = $this->resource->getConnection();
        $columns = $connection->describeTable($table);

        return array_key_exists($field, $columns);
    }
}
