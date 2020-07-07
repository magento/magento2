<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\MediaContentApi\Model\GetAssetIdByContentStatusInterface;

/**
 * Class GetAssetIdByEavContentStatus
 */
class GetAssetIdByEavContentStatus implements GetAssetIdByContentStatusInterface
{
    private const TABLE_CONTENT_ASSET = 'media_content_asset';
    private const TABLE_EAV_ATTRIBUTE = 'eav_attribute';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var string
     */
    private $entityEavTypeTable;

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @var int
     */
    private $entityTypeId;

    /**
     * @var array
     */
    private $valueMap;

    /**
     * GetEavContentIdByStatus constructor.
     * @param ResourceConnection $resource
     * @param string $entityEavTypeTable
     * @param string $attributeCode
     * @param string $entityType
     * @param int $entityTypeId
     * @param array $valueMap
     */
    public function __construct(
        ResourceConnection $resource,
        string $entityEavTypeTable,
        string $attributeCode,
        string $entityType,
        int $entityTypeId,
        array $valueMap = []
    ) {
        $this->connection = $resource;
        $this->entityEavTypeTable = $entityEavTypeTable;
        $this->attributeCode = $attributeCode;
        $this->entityType = $entityType;
        $this->entityTypeId = $entityTypeId;
        $this->valueMap = $valueMap;
    }

    /**
     * @param string $value
     * @return array
     */
    public function execute(string $value): array
    {
        $statusAttributeId = $this->getAttributeId();
        $sql = $this->connection->getConnection()->select()->from(
            ['asset_content_table' => $this->connection->getTableName(self::TABLE_CONTENT_ASSET)],
            ['asset_id']
        )->where(
            'entity_type = ?',
            $this->entityType
        )->joinInner(
            ['entity_eav_type' => $this->connection->getTableName($this->entityEavTypeTable)],
            'asset_content_table.entity_id = entity_eav_type.entity_id AND entity_eav_type.attribute_id = ' .
            $statusAttributeId,
            []
        )->where(
            'entity_eav_type.value = ?',
            $this->getValueFromMap($value)
        );

        $result = $this->connection->getConnection()->fetchAll($sql);

        return array_map(function ($item) {
            return $item['asset_id'];
        }, $result);
    }

    /**
     * @return string
     */
    private function getAttributeId(): string
    {
        $sql = $this->connection->getConnection()->select()->from(
            ['eav' => $this->connection->getTableName(self::TABLE_EAV_ATTRIBUTE)],
            ['attribute_id']
        )->where(
            'entity_type_id = ?',
            $this->entityTypeId
        )->where(
            'attribute_code = ?',
            $this->attributeCode
        );

        return $this->connection->getConnection()->fetchOne($sql);
    }

    /**
     * @param string $value
     * @return string
     */
    private function getValueFromMap(string $value): string
    {
        if (count($this->valueMap) > 0 && array_key_exists($value, $this->valueMap)) {
            return $this->valueMap[$value];
        }
        return $value;
    }
}
