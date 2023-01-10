<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Model\GetAssetIdsByContentFieldInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class responsible to return Asset ids by product store
 */
class GetAssetIdsByProductStore implements GetAssetIdsByContentFieldInterface
{
    private const TABLE_CONTENT_ASSET = 'media_content_asset';
    private const ENTITY_TYPE = 'catalog_product';
    private const FIELD_TABLE = 'catalog_product_website';
    private const ID_COLUMN = 'product_id';
    private const FIELD_COLUMN = 'website_id';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * GetAssetIdsByProductStore constructor.
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
    }

    /**
     * @inheritDoc
     */
    public function execute(string $value): array
    {
        $store = $this->storeRepository->getById($value);
        $sql = $this->connection->getConnection()->select()->from(
            ['asset_content_table' => $this->connection->getTableName(self::TABLE_CONTENT_ASSET)],
            ['asset_id']
        )->where(
            'entity_type = ?',
            self::ENTITY_TYPE
        )->joinInner(
            ['field_table' => $this->connection->getTableName(self::FIELD_TABLE)],
            'asset_content_table.entity_id = field_table.' . self::ID_COLUMN,
            []
        )->where(
            'field_table.' . self::FIELD_COLUMN . ' = ?',
            $store->getWebsiteId()
        );

        return $this->connection->getConnection()->fetchCol($sql);
    }
}
