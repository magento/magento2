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
    private const PATTERN_VALID_CHARACTERS = '/[^A-Za-z0-9\_\.\,\-]/';
    private const PATTERN_COMMA = '/,+/';
    private const SPECIAL_CHARACTERS = '/[.,*?!@#$&-_ ]+$/';

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
        $value = $this->formatKeywordValue($filter->getValue());

        foreach ($this->splitRequestString($value) as $keyword) {
            $collection->addFieldToFilter(
                [self::TABLE_ALIAS . '.title', self::TABLE_ALIAS . '.id'],
                [
                    ['like' => sprintf('%%%s%%', $keyword)],
                    ['in' => $this->getAssetIdsByKeyword($keyword)]
                ]
            );
        }

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

        $select = $connection->select();
        $select->from(
            ['asset_keywords_table' => $this->connection->getTableName(self::TABLE_ASSET_KEYWORD)],
            ['id']
        )->where(
            'keyword = ?',
            $value
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
     * Split the request string
     * $escapedKeywords removes all invalid characters and replaces with comma/s
     * $formattedKeywords removes succeeding commas from $escapedKeywords
     *
     * @param string $value
     * @return array
     */
    private function splitRequestString(string $value): array
    {
        $escapedKeywords = preg_replace(self::PATTERN_VALID_CHARACTERS, ',', $value);
        $formattedKeywords = preg_replace(self::PATTERN_COMMA, ',', $escapedKeywords);

        $keywordValues = [];
        if (!is_array($value)) {
            $keywordValues = explode(',', $formattedKeywords);
        }

        return $keywordValues;
    }

    /**
     * Format request string to remove special characters at the end of the string
     *
     * @param string $value
     * @return string
     */
    private function formatKeywordValue(string $value): string
    {
        if (preg_match(self::SPECIAL_CHARACTERS, $value) > 0) {
            $value = preg_replace(self::SPECIAL_CHARACTERS, "", $value);
        }

        return $value;
    }
}
