<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model\ResourceModel;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
    private const ID_COLUMN = 'entity_id';

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
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * GetAssetIdsByCategoryStore constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreRepositoryInterface $storeRepository
     * @param GroupRepositoryInterface $storeGroupRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        ResourceConnection $resource,
        StoreRepositoryInterface $storeRepository,
        GroupRepositoryInterface $storeGroupRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->connection = $resource;
        $this->storeRepository = $storeRepository;
        $this->storeGroupRepository = $storeGroupRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $value): array
    {
        try {
            $storeView = $this->storeRepository->getById($value);
            $storeGroup = $this->storeGroupRepository->get($storeView->getStoreGroupId());
            $rootCategory = $this->categoryRepository->get($storeGroup->getRootCategoryId());
        } catch (NoSuchEntityException $exception) {
            return [];
        }

        $sql = $this->connection->getConnection()->select()->from(
            ['asset_content_table' => $this->connection->getTableName(self::TABLE_CONTENT_ASSET)],
            ['asset_id']
        )->joinInner(
            ['category_table' => $this->connection->getTableName(self::TABLE_CATALOG_CATEGORY)],
            'asset_content_table.entity_id = category_table.' . self::ID_COLUMN,
            []
        )->where(
            'entity_type = ?',
            self::ENTITY_TYPE
        )->where(
            'path LIKE ?',
            $rootCategory->getPath() . '%'
        );

        return $this->connection->getConnection()->fetchCol($sql);
    }
}
