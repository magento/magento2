<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model\ResourceModel;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\MediaContentApi\Model\GetAssetIdsByContentFieldInterface;

/**
 * Class responsible to return Asset id by eav content field
 */
class GetAssetIdsByEavContentField implements GetAssetIdsByContentFieldInterface
{
    private const TABLE_CONTENT_ASSET = 'media_content_asset';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @var string
     */
    private $entityTable;

    /**
     * @var array
     */
    private $valueMap;

    /**
     * GetAssetIdsByEavContentField constructor.
     *
     * @param ResourceConnection $resource
     * @param Config $config
     * @param string $attributeCode
     * @param string $entityType
     * @param string $entityTable
     * @param array $valueMap
     */
    public function __construct(
        ResourceConnection $resource,
        Config $config,
        string $attributeCode,
        string $entityType,
        string $entityTable,
        array $valueMap = []
    ) {
        $this->connection = $resource;
        $this->config = $config;
        $this->attributeCode = $attributeCode;
        $this->entityType = $entityType;
        $this->entityTable = $entityTable;
        $this->valueMap = $valueMap;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $value): array
    {
        $attribute = $this->config->getAttribute($this->entityType, $this->attributeCode);

        $sql = $this->connection->getConnection()->select()->from(
            ['asset_content_table' => $this->connection->getTableName(self::TABLE_CONTENT_ASSET)],
            ['asset_id']
        )->where(
            'entity_type = ?',
            $this->entityType
        )->joinInner(
            ['entity_table' => $this->connection->getTableName($this->entityTable)],
            'asset_content_table.entity_id = entity_table.entity_id',
            []
        )->joinInner(
            ['entity_eav_type' => $this->connection->getTableName($attribute->getBackendTable())],
            'entity_table.' . $attribute->getEntityIdField() . ' = entity_eav_type.' . $attribute->getEntityIdField() .
            ' AND entity_eav_type.attribute_id = ' . $attribute->getAttributeId(),
            []
        )->where(
            'entity_eav_type.value = ?',
            $this->getValueFromMap($value)
        );

        return $this->connection->getConnection()->fetchCol($sql);
    }

    /**
     * Get a value from a value map
     *
     * @param string $value
     * @return string
     */
    private function getValueFromMap(string $value): string
    {
        return $this->valueMap[$value] ?? $value;
    }
}
