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
 * Search request string applied to title, description and keywords. More words in the string narrows down the results
 */
class Keyword implements CustomFilterInterface
{
    private const TABLE_ALIAS = 'main_table';
    private const TABLE_KEYWORDS = 'media_gallery_asset_keyword';
    private const TABLE_ASSET_KEYWORD = 'media_gallery_keyword';
    private const SPECIAL_CHARACTERS = '/[.,*?!@#$&-_]/';

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
     * @inheritdoc
     */
    public function apply(Filter $filter, AbstractDb $collection): bool
    {
        foreach ($this->getKeywords($filter->getValue()) as $keyword) {
            $collection->addFieldToFilter(
                [
                    self::TABLE_ALIAS . '.title',
                    self::TABLE_ALIAS . '.description',
                    self::TABLE_ALIAS . '.id',
                ],
                [
                    ['like' => sprintf('%%%s%%', $keyword)],
                    ['like' => sprintf('%%%s%%', $keyword)],
                    ['in' => $this->getAssetIdsByKeyword($keyword)]
                ]
            );
        }

        return true;
    }

    /**
     * Retrieve all asset id's by keyword exact match
     *
     * @param string $keyword
     * @return array
     */
    private function getAssetIdsByKeyword(string $keyword): array
    {
        $connection = $this->connection->getConnection();

        $select = $connection->select();
        $select->from(
            ['asset_keywords_table' => $this->connection->getTableName(self::TABLE_ASSET_KEYWORD)],
            ['id']
        )->where(
            'keyword = ?',
            $keyword
        )->joinInner(
            ['keywords_table' => $this->connection->getTableName(self::TABLE_KEYWORDS)],
            'keywords_table.keyword_id = asset_keywords_table.id',
            ['asset_id']
        );

        return $connection->fetchAssoc(
            $connection->select()->from(
                $select,
                ['asset_id']
            )
        );
    }

    /**
     * Remove special characters and split the request string into keywords
     *
     * @param string $value
     * @return array
     */
    private function getKeywords(string $value): array
    {
        return array_filter(explode(' ', preg_replace(self::SPECIAL_CHARACTERS, ' ', $value)));
    }
}
