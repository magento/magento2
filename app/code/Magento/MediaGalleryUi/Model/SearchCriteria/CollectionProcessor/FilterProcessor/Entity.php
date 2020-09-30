<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Custom filter to filter collection by entity type
 */
class Entity implements CustomFilterInterface
{
    private const TABLE_ALIAS = 'main_table';
    private const TABLE_MEDIA_CONTENT_ASSET = 'media_content_asset';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @param ResourceConnection $resource
     * @param string $entityType
     */
    public function __construct(ResourceConnection $resource, string $entityType)
    {
        $this->connection = $resource;
        $this->entityType = $entityType;
    }

    /**
     * @inheritDoc
     */
    public function apply(Filter $filter, AbstractDb $collection): bool
    {
        $ids = $filter->getValue();
        if (is_array($ids)) {
            $collection->addFieldToFilter(
                [self::TABLE_ALIAS . '.id'],
                [
                    ['in' => $this->getSelectByEntityIds($ids)]
                ]
            );
        }
        return true;
    }

    /**
     * Return asset ids by entity type
     *
     * @param array $ids
     * @return array
     */
    private function getSelectByEntityIds(array $ids): array
    {
        $connection = $this->connection->getConnection();

        return $connection->fetchAssoc(
            $connection->select()->from(
                ['asset_content_table' => $this->connection->getTableName(self::TABLE_MEDIA_CONTENT_ASSET)],
                ['asset_id']
            )->where(
                'entity_type = ?',
                $this->entityType
            )->where(
                'entity_id IN (?)',
                $ids
            )
        );
    }
}
