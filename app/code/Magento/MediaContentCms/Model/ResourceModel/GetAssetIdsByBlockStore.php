<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCms\Model\ResourceModel;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Model\GetAssetIdsByContentFieldInterface;

/**
 * Class responsible to return Asset id by content field
 */
class GetAssetIdsByBlockStore implements GetAssetIdsByContentFieldInterface
{
    private const TABLE_CONTENT_ASSET = 'media_content_asset';
    private const ENTITY_TYPE = 'cms_block';
    private const STORE_FIELD = 'store_id';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * GetAssetIdsByContentField constructor.
     *
     * @param ResourceConnection $resource
     * @param BlockRepositoryInterface $blockRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ResourceConnection $resource,
        BlockRepositoryInterface $blockRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->connection = $resource;
        $this->blockRepository = $blockRepository;
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
            $this->getBlockIdsByStore((int) $value)
        );

        return $this->connection->getConnection()->fetchCol($sql);
    }

    /**
     * Get block ids by store
     *
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    private function getBlockIdsByStore(int $storeId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(self::STORE_FIELD, $storeId)
            ->create();

        $searchResult = $this->blockRepository->getList($searchCriteria);

        return array_map(function (BlockInterface $block) {
            return $block->getId();
        }, $searchResult->getItems());
    }
}
