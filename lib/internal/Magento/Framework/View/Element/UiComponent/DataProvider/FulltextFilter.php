<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use \Magento\Framework\Model\Resource\Db\AbstractDb as DbResource;

/**
 * Class Fulltext
 */
class FulltextFilter implements FilterApplierInterface
{
    /**
     * Returns list of columns from fulltext index (doesn't support more then one FTI per table)
     *
     * @param DbResource $resource
     * @param string $indexTable
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getFulltextIndexColumns(DbResource $resource, $indexTable)
    {
        $indexes = $resource->getReadConnection()->getIndexList($indexTable);
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
     * @param array $filters
     * @return void
     */
    public function apply(DbCollection $collection, $filters)
    {
        $columns = $this->getFulltextIndexColumns($collection->getResource(), $collection->getMainTable());
        if (!$columns) {
            return;
        }
        foreach ($filters as $filter) {
            $collection->getSelect()
                ->where(
                    'MATCH(' . implode(',', $columns) . ') AGAINST(?)',
                    $filter['condition']
                );
        }
    }
}
