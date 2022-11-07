<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCms\Model\ResourceModel;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Model\GetAssetIdsByContentFieldInterface;

/**
 * Class responsible to return Asset id by content field
 */
class GetAssetIdsByPageStore implements GetAssetIdsByContentFieldInterface
{
    private const TABLE_CONTENT_ASSET = 'media_content_asset';
    private const ENTITY_TYPE = 'cms_page';
    private const STORE_FIELD = 'store_id';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * GetAssetIdsByContentField constructor.
     *
     * @param ResourceConnection $resource
     * @param PageRepositoryInterface $pageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ResourceConnection $resource,
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->connection = $resource;
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
            self::ENTITY_TYPE
        )->where(
            'entity_id IN (?)',
            $this->getPageIdsByStore((int) $value)
        );

        return $this->connection->getConnection()->fetchCol($sql);
    }

    /**
     * Get page ids by store
     *
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    private function getPageIdsByStore(int $storeId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(self::STORE_FIELD, $storeId)
            ->create();

        $searchResult = $this->pageRepository->getList($searchCriteria);

        return array_map(function (PageInterface $page) {
            return $page->getId();
        }, $searchResult->getItems());
    }
}
