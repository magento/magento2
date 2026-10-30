<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Ui\Component\Listing;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Helper;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter as BaseFulltextFilter;

/**
 * Fulltext keyword search for the admin Sales Order grid.
 *
 * Delegates to the parent's MATCH...AGAINST search unchanged, restoring index-backed performance and
 * multi-term OR semantics for the common case, and additionally matches via LIKE any search term InnoDB's
 * fulltext engine would silently drop from its index: stopwords and tokens shorter than the minimum indexed
 * token size.
 */
class FulltextFilter extends BaseFulltextFilter
{
    /**
     * InnoDB's built-in default fulltext stopword list: SELECT * FROM INFORMATION_SCHEMA.INNODB_FT_DEFAULT_STOPWORD;
     */
    private const DEFAULT_STOPWORDS = [
        'a','about','an','are','as','at','be','by','com','de','en','for','from','how','i','in','is','it','la','of','on',
        'or','that','the','this','to','was','what','when','where','who','will','with','und','the','www'
    ];

    /**
     * Matches InnoDB's innodb_ft_min_token_size default.
     */
    private const DEFAULT_MIN_TOKEN_SIZE = 3;

    /**
     * @param Helper $dbHelper
     * @param string[] $stopwords Search terms to exclude from MATCH...AGAINST and match via LIKE instead.
     * @param int $minTokenSize Minimum term length eligible for MATCH...AGAINST; shorter terms use LIKE.
     */
    public function __construct(
        private readonly Helper $dbHelper,
        private readonly array  $stopwords = self::DEFAULT_STOPWORDS,
        private readonly int    $minTokenSize = self::DEFAULT_MIN_TOKEN_SIZE
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(Collection $collection, Filter $filter): void
    {
        if (!$collection instanceof AbstractDb) {
            throw new \InvalidArgumentException('Database collection required.');
        }

        $raw = trim((string) $filter->getValue());
        if ($raw === '') {
            return;
        }

        $tokens = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $ignoredTokens = array_filter($tokens, fn (string $token): bool => $this->isTokenIgnored($token));

        if (!$ignoredTokens) {
            // nothing InnoDB's fulltext index would drop, so no LIKE fallback
            parent::apply($collection, $filter);
            return;
        }

        $select = $collection->getSelect();
        $baseline = $select->getPart(Select::WHERE);

        parent::apply($collection, $filter);

        $addedParts = array_map(
            fn (string $part): string => $this->stripLeadingBoolean($part),
            array_diff($select->getPart(Select::WHERE), $baseline)
        );
        $select->reset(Select::WHERE);
        $select->setPart(Select::WHERE, $baseline);

        $addedParts = array_merge($addedParts, $this->buildLikeConditions($collection, $ignoredTokens));

        if ($addedParts) {
            $select->where('(' . implode(') ' . Select::SQL_OR . ' (', $addedParts) . ')');
        }
    }

    /**
     * Build a LIKE condition per ignored token, per fulltext-indexed column.
     *
     * @param AbstractDb $collection
     * @param string[] $tokens
     * @return string[]
     */
    private function buildLikeConditions(AbstractDb $collection, array $tokens): array
    {
        $mainTable = $collection->getMainTable();
        $columns = $this->getFulltextIndexColumns($collection, $mainTable);
        if (!$columns) {
            return [];
        }
        $columns = $this->addTableAliasToColumns($columns, $collection, $mainTable);

        $connection = $collection->getConnection();
        $conditions = array_map(
            fn (string $token) => array_map(
                fn (string $column) => $connection->prepareSqlCondition(
                    $column,
                    ['like' => $this->dbHelper->escapeLikeValue($token, ['position' => 'any'])]
                ),
                $columns
            ),
            $tokens
        );

        return array_merge(...$conditions);
    }

    /**
     * Whether a search term would be silently dropped by InnoDB's fulltext index (stopword or too short).
     *
     * @param string $token
     * @return bool
     */
    private function isTokenIgnored(string $token): bool
    {
        return mb_strlen($token) < $this->minTokenSize || in_array(mb_strtolower($token), $this->stopwords, true);
    }

    /**
     * Strip a leading boolean keyword (AND/OR) Zend_Db_Select prefixes WHERE parts with.
     *
     * @param string $part
     * @return string
     */
    private function stripLeadingBoolean(string $part): string
    {
        return preg_replace('/^(' . Select::SQL_OR . '|' . Select::SQL_AND . ')\s+/i', '', trim($part), 1);
    }
}
