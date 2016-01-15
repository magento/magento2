<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\Filter;

/**
 * Class Fulltext
 */
class FulltextFilter implements FilterApplierInterface
{
    /**
     * Returns list of columns from fulltext index (doesn't support more then one FTI per table)
     *
     * @param AbstractDb $collection
     * @param string $indexTable
     * @return array
     */
    protected function getFulltextIndexColumns(AbstractDb $collection, $indexTable)
    {
        $indexes = $collection->getConnection()->getIndexList($indexTable);
        foreach ($indexes as $index) {
            if (strtoupper($index['INDEX_TYPE']) == 'FULLTEXT') {
                return $index['COLUMNS_LIST'];
            }
        }
        return [];
    }

    /**
     * Apply fulltext filters
     *
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     */
    public function apply(Collection $collection, Filter $filter)
    {
        if (!$collection instanceof AbstractDb) {
            throw new \InvalidArgumentException('Database collection required.');
        }

        /** @var AbstractDb $collection */
        $columns = $this->getFulltextIndexColumns($collection, $collection->getResource()->getMainTable());
        if (!$columns) {
            return;
        }

        $collection->getSelect()
            ->where(
                'MATCH(' . implode(',', $columns) . ') AGAINST(?)',
                $filter->getValue()
            );
    }
}
