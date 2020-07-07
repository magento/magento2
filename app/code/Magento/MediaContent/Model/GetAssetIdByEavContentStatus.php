<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\MediaContentApi\Model\GetAssetIdByContentStatusInterface;

/**
 * Class GetAssetIdByEavContentStatus
 */
class GetAssetIdByEavContentStatus implements GetAssetIdByContentStatusInterface
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
     * @var array
     */
    private $valueMap;

    /**
     * GetAssetIdByEavContentStatus constructor.
     * @param ResourceConnection $resource
     * @param Config $config
     * @param string $attributeCode
     * @param string $entityType
     * @param array $valueMap
     */
    public function __construct(
        ResourceConnection $resource,
        Config $config,
        string $attributeCode,
        string $entityType,
        array $valueMap = []
    ) {
        $this->connection = $resource;
        $this->config = $config;
        $this->attributeCode = $attributeCode;
        $this->entityType = $entityType;
        $this->valueMap = $valueMap;
    }

    /**
     * @param string $value
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $value): array
    {
        $statusAttribute = $this->config->getAttribute($this->entityType, $this->attributeCode);

        $sql = $this->connection->getConnection()->select()->from(
            ['asset_content_table' => $this->connection->getTableName(self::TABLE_CONTENT_ASSET)],
            ['asset_id']
        )->where(
            'entity_type = ?',
            $this->entityType
        )->joinInner(
            ['entity_eav_type' => $statusAttribute->getBackendTable()],
            'asset_content_table.entity_id = entity_eav_type.' . $statusAttribute->getEntityIdField() .
            ' AND entity_eav_type.attribute_id = ' .
            $statusAttribute->getAttributeId(),
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
