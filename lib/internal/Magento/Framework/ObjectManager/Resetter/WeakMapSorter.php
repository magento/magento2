<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Resetter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use WeakReference;
use WeakMap;

/**
 * Sorts a WeakMap into an ordered array of WeakReference and reset them in order.
 */
class WeakMapSorter
{

    public const DEFAULT_SORT_VALUE = 5000;

    public const MAX_SORT_VALUE = 10000;

    /**
     * Constructor
     *
     * @param array $sortOrder
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(private array $sortOrder)
    {
        // Note: Even though they are declared as xsi:type="number", they are still strings, so we convert them here.
        foreach ($this->sortOrder as &$value) {
            $value = (int)$value;
        }
    }

    /**
     * Sorts the WeakMap into a WeakReference list
     *
     * @param WeakMap $weakmap
     * @return WeakReference[]
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
     * Gets sort value for the specified object
     *
     * @param object $object
     * @return int
     */
    private function getSortValueOfObject(object $object) : int
    {
        $className = get_class($object);
        if (array_key_exists($className, $this->sortOrder)) {
            return $this->sortOrder[$className];
        }
        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($parentClass = $className; $parentClass = get_parent_class($parentClass);) {
            if (array_key_exists($parentClass, $this->sortOrder)) {
                $sortValue = $this->sortOrder[$parentClass];
                $this->sortOrder[$className] = $sortValue;
                return $sortValue;
            }
        }
        $sortValue = static::DEFAULT_SORT_VALUE;
        foreach ($this->sortOrder as $sortOrderKey => $sortOrderValue) {
            if ($object instanceof $sortOrderKey) {
                $sortValue = $sortOrderValue;
                break;
            }
        }
        $this->sortOrder[$className] = $sortValue;
        return $sortValue;
    }
}
