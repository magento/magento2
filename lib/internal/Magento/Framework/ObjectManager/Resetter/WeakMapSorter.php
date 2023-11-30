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
class WeakMapSorter implements ResetAfterRequestInterface
{

    private const DEFAULT_SORT_VALUE = 5000;

    private const MAX_SORT_VALUE = 9000;

    /**
     * @var SortableReferenceObject[]
     */
    private array $sortableReferenceList = [];

    /**
     * Constructor
     *
     * @param WeakMap $weakmap
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct (WeakMap $weakmap, array $resettableArgs)
    {
        foreach ($weakmap as $weakMapObject => $value) {
            if (!$weakMapObject || !($weakMapObject instanceof ResetAfterRequestInterface)) {
                continue;
            }
            $sortValue = $this->getSortValueOfObject($weakMapObject, $resettableArgs);
            $weakReference = WeakReference::create($weakMapObject);
            $this->sortableReferenceList[] = new SortableReferenceObject($weakReference, $sortValue);
        }
        usort(
            $this->sortableReferenceList,
            fn(SortableReferenceObject $a, SortableReferenceObject  $b) => $a->getSort() - $b->getSort()
        );
    }

    /**
     * @param object $object
     * @return int
     */
    public function getSortValueOfObject(object $object, array $resettableArgs) : int
    {
        if (!in_array($object, $resettableArgs)) {
            return static::DEFAULT_SORT_VALUE;
        }
        $args = ObjectManager::getInstance()->get(\Magento\Framework\ObjectManager\Config\Config::class)->getArguments(get_class($object));

        return self::MAX_SORT_VALUE;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        foreach ($this->sortableReferenceList as $sortableReferenceObject) {
            $sortableReferenceObject->_resetState();
        }
    }
}
