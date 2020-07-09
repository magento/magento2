<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCms\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Model\GetAssetIdByContentFieldInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class responsible to return Asset id by content field
 */
class GetAssetIdByContentField implements GetAssetIdByContentFieldInterface
{
    private const TABLE_CONTENT_ASSET = 'media_content_asset';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @var string
     */
    private $fieldTable;

    /**
     * @var string
     */
    private $fieldColumn;

    /**
     * @var string
     */
    private $idColumn;


    /**
     * GetAssetIdByContentField constructor.
     *
     * @param ResourceConnection $resource
     * @param string $entityType
     * @param string $fieldTable
     * @param string $idColumn
     * @param string $fieldColumn
     */
    public function __construct(
        ResourceConnection $resource,
        string $entityType,
        string $fieldTable,
        string $idColumn,
        string $fieldColumn
    ) {
        $this->connection = $resource;
        $this->entityType = $entityType;
        $this->fieldTable = $fieldTable;
        $this->idColumn = $idColumn;
        $this->fieldColumn = $fieldColumn;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $value): array
    {
        $sql = $this->connection->getConnection()->select()->from(
            ['asset_content_table' => $this->connection->getTableName(self::TABLE_CONTENT_ASSET)],
            ['asset_id']
        )->where(
            'entity_type = ?',
            $this->entityType
        )->joinInner(
            ['field_table' => $this->connection->getTableName($this->fieldTable)],
            'asset_content_table.entity_id = field_table.' . $this->idColumn,
            []
        )->where(
            'field_table.' . $this->fieldColumn . ' = ?',
            $value
        );

        $result = $this->connection->getConnection()->fetchAll($sql);

        return array_map(function ($item) {
            return $item['asset_id'];
        }, $result);
    }
}
