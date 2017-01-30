<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as DbResource;
use Magento\Framework\Api\Filter;

/**
 * Class Fulltext
 */
class FulltextFilter implements FilterApplierInterface
{
    /**
     * Returns list of columns from fulltext index (doesn't support more then one FTI per table)
     *
     * @param DbCollection $collection
     * @param string $indexTable
     * @return array
     */
    protected function getFulltextIndexColumns(DbCollection $collection, $indexTable)
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
     * @param DbCollection $collection
     * @param Filter $filter
     * @return void
     */
    public function apply(DbCollection $collection, Filter $filter)
    {
        $columns = $this->getFulltextIndexColumns($collection, $collection->getMainTable());
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
