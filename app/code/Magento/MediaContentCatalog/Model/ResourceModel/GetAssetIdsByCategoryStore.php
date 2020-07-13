<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model\ResourceModel;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Model\GetAssetIdsByContentFieldInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class responsible to return Asset id by category store
 */
class GetAssetIdsByCategoryStore implements GetAssetIdsByContentFieldInterface
{
    private const TABLE_CONTENT_ASSET = 'media_content_asset';
    private const TABLE_CATALOG_CATEGORY = 'catalog_category_entity';
    private const ENTITY_TYPE = 'catalog_category';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $storeGroupRepository;

    /**
     * GetAssetIdsByCategoryStore constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreRepositoryInterface $storeRepository
     * @param GroupRepositoryInterface $storeGroupRepository
     */
    public function __construct(
        ResourceConnection $resource,
        StoreRepositoryInterface $storeRepository,
        GroupRepositoryInterface $storeGroupRepository
    ) {
        $this->connection = $resource;
        $this->storeRepository = $storeRepository;
        $this->storeGroupRepository = $storeGroupRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $value): array
    {
        $storeView = $this->storeRepository->getById($value);
        $storeGroup = $this->storeGroupRepository->get($storeView->getStoreGroupId());
        $categoryIds = $this->getCategoryIdsByRootCategory((int) $storeGroup->getRootCategoryId());
        $sql = $this->connection->getConnection()->select()->from(
            ['asset_content_table' => $this->connection->getTableName(self::TABLE_CONTENT_ASSET)],
            ['asset_id']
        )->where(
            'entity_type = ?',
            self::ENTITY_TYPE
        )->where(
            'entity_id IN (?)',
            $categoryIds
        );

        return $this->connection->getConnection()->fetchCol($sql);
    }

    /**
     * This function returns an array of category ids that have content and are under the root parameter
     *
     * @param $rootCategoryId
     * @return array
     */
    private function getCategoryIdsByRootCategory(int $rootCategoryId): array
    {
        $result = $this->getCategoryIdsAndPath();

        $result = array_filter($result, function ($item) use ($rootCategoryId) {
            $pathArray = explode('/', $item['path']);
            $isInPath = false;
            foreach ($pathArray as $id) {
                if ($id == $rootCategoryId) {
                    $isInPath = true;
                }
            }
            return  $isInPath;
        });

        return array_map(function ($item) {
            return $item['entity_id'];
        }, $result);
    }

    /**
     * This function returns an array of category_id and path of categories that have content
     *
     * @return array
     */
    private function getCategoryIdsAndPath(): array
    {
        $contentCategoriesSql = $this->connection->getConnection()->select()->from(
            ['asset_content_table' => $this->connection->getTableName(self::TABLE_CONTENT_ASSET)],
            ['entity_id']
        )->where(
            'entity_type = ?',
            self::ENTITY_TYPE
        )->joinInner(
            ['category_table' => $this->connection->getTableName(self::TABLE_CATALOG_CATEGORY)],
            'asset_content_table.entity_id = category_table.entity_id',
            ['path']
        );

        return $this->connection->getConnection()->fetchAll($contentCategoriesSql);
    }
}
