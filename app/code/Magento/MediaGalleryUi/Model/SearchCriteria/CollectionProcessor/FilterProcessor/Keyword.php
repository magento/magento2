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

class Keyword implements CustomFilterInterface
{
    private const TABLE_ALIAS = 'main_table';
    private const TABLE_KEYWORDS = 'media_gallery_asset_keyword';
    private const TABLE_ASSET_KEYWORD = 'media_gallery_keyword';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource;
    }

    /**
     * @inheritDoc
     */
    public function apply(Filter $filter, AbstractDb $collection): bool
    {
        $value = $filter->getValue();

        $collection->addFieldToFilter(
            [self::TABLE_ALIAS . '.title', self::TABLE_ALIAS . '.id'],
            [
                ['like' => sprintf('%%%s%%', $value)],
                ['in' => $this->getAssetIdsByKeyword($value)]
            ]
        );

        return true;
    }

    /**
     * Return  asset ids by keyword
     *
     * @param string $value
     * @return array
     */
    private function getAssetIdsByKeyword(string $value): array
    {
        $connection = $this->connection->getConnection();

        return $connection->fetchAssoc(
            $connection->select()->from(
                $connection->select()
                    ->from(
                        ['asset_keywords_table' => $this->connection->getTableName(self::TABLE_ASSET_KEYWORD)],
                        ['id']
                    )->where(
                        'keyword = ?',
                        $value
                    )->joinInner(
                        ['keywords_table' => $this->connection->getTableName(self::TABLE_KEYWORDS)],
                        'keywords_table.keyword_id = asset_keywords_table.id',
                        ['asset_id']
                    ),
                ['asset_id']
            )
        );
    }
}
