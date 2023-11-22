<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\ApplicationStateComparator\Resetter;

use WeakReference;
use WeakMap;

/**
 * Sorts a WeakMap into an ordered array of WeakReference and reset them in order.
 */
class WeakMapSorter
{

    private const DEFAULT_SORT_VALUE = 5000;

    /**
     * @param WeakMap $weakmap
     * @return WeakReference[]
     */
    public function sortWeakMapIntoWeakReferenceList(WeakMap $weakmap) : array
    {
        /** @var SortableReferenceObject[] */
        $sortableReferenceList = [];
        foreach ($weakmap as $weakMapObject => $value) {
            if (!$weakMapObject) {
                continue;
            }
            $sortValue = $this->getSortValueOfObject($weakMapObject);
            $weakReference = WeakReference::create($weakMapObject);
            $sortableReferenceList[] = new SortableReferenceObject($weakReference, $sortValue);
        }
        usort(
            $sortableReferenceList,
            fn(SortableReferenceObject $a, SortableReferenceObject  $b) => $a->getSort() - $b->getSort()
        );
        $returnValue = [];
        foreach ($sortableReferenceList as $sortableReference) {
            $returnValue[] = $sortableReference->getWeakReference();
        }
        return $returnValue;
    }

    /**
     *  This is just temporary workaround for ACPT-1666
     *
     * TODO: This needs to be changed in ACPT-1666
     *
     * @param object $object
     * @return int
     */
    private function getSortValueOfObject(object $object) : int
    {
        if ($object instanceof \Magento\Framework\Session\SessionManager) {
            return 1000;
        }
        if ($object instanceof \Magento\CatalogStaging\Plugin\Catalog\Model\Indexer\AbstractFlatState) {
            return 9000;
        }
        if ($object instanceof \Magento\Staging\Model\Update\VersionHistory) {
            return 9000;
        }
        if ($object instanceof \Magento\Staging\Model\UpdateRepositoryCache) {
            return 9000;
        }
        if ($object instanceof \Magento\Framework\DB\Adapter\Pdo\Mysql) {
            return 9999;
        }
        if ($object instanceof \Magento\Framework\DB\Logger\LoggerProxy) {
            return 9999;
        }
        if ($object instanceof \Magento\Framework\HTTP\LaminasClient) {
            return 9999;
        }
        if ($object instanceof \Magento\Framework\App\Cache\State) {
            return 10000;
        }
        return static::DEFAULT_SORT_VALUE;
    }
}
