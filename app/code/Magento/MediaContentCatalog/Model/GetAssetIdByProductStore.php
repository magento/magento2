<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Model\GetAssetIdByContentFieldInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class responsible to return Asset id by content field
 */
class GetAssetIdByProductStore implements GetAssetIdByContentFieldInterface
{
    private const TABLE_CONTENT_ASSET = 'media_content_asset';
    private const ENTITY_STOREVIEW_RELATION = 'store_view';
    private const ENTITY_STOREGROUP_RELATION = 'store_group';
    private const ENTITY_WEBSITE_RELATION = 'website';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

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
     * GetAssetIdByProductStore constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        ResourceConnection $resource,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->connection = $resource;
        $this->storeRepository = $storeRepository;
        $this->entityType = 'catalog_product';
        $this->fieldTable = 'catalog_product_website';
        $this->idColumn = 'product_id';
        $this->fieldColumn = 'website_id';
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(string $value): array
    {
        $store = $this->storeRepository->getById($value);
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
            $store->getWebsiteId()
        );

        $result = $this->connection->getConnection()->fetchAll($sql);

        return array_map(function ($item) {
            return $item['asset_id'];
        }, $result);
    }
}
