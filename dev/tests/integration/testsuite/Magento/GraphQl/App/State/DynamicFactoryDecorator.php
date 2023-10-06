<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use WeakMap;
use WeakReference;

/**
 * Dynamic Factory Decorator for State test.  Stores collected properties from created objects in WeakMap
 */
class DynamicFactoryDecorator extends Developer implements ResetAfterRequestInterface
{
    /** @var WeakMap $weakMap Where CollectedObject is stored after object is created by us */
    private WeakMap $weakMap;

    //phpcs:ignore
    private readonly Collector $collector;

    //phpcs:ignore
    private readonly array $skipList;

    /**
     * Constructs this instance by copying $developer
     *
     * @param Developer $developer
     * @param ObjectManager $objectManager
     */
    public function __construct(Developer $developer, ObjectManager $objectManager)
    {
        /* Note: PHP doesn't have copy constructors, so we have to use get_object_vars,
         * but luckily all the properties in the superclass are protected. */
        $properties = get_object_vars($developer);
        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
        $this->objectManager = $objectManager;
        $this->weakMap = new WeakMap();
        $skipListAndFilterList =  new SkipListAndFilterList;
        $this->skipList = $skipListAndFilterList->getSkipList('', CompareType::CompareConstructedAgainstCurrent);
        $this->collector = new Collector($this->objectManager, $skipListAndFilterList);
        $this->objectManager->addSharedInstance($skipListAndFilterList, SkipListAndFilterList::class);
        $this->objectManager->addSharedInstance($this->collector, Collector::class);
    }

    /**
     * @inheritDoc
     */
    public function create($type, array $arguments = [])
    {
        $object = parent::create($type, $arguments);
        if (!array_key_exists(get_class($object), $this->skipList)) {
            $this->weakMap[$object] =
                $this->collector->getPropertiesFromObject($object, CompareType::CompareConstructedAgainstCurrent);
        }
        return $object;
    }

    /**
     * Reset state for all instances that we've created
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function _resetState(): void
    {
        /* Note: We force garbage collection to clean up cyclic referenced objects before _resetState()
        This is to prevent calling _resetState() on objects that will be destroyed by garbage collector. */
        gc_collect_cycles();
        /* Note: we can't iterate weakMap itself because it gets indirectly modified (shrinks) as some of the
         * service classes that get reset will destruct some of the other service objects.  The iterator to WeakMap
         * returns actual objects, not WeakReferences.  Therefore, we create a temporary list of weak references which
         *  is safe to iterate. */
        $weakReferenceListToReset = [];
        foreach ($this->weakMap as $weakMapObject => $value) {
            if ($weakMapObject instanceof ResetAfterRequestInterface) {
                $weakReferenceListToReset[] = WeakReference::create($weakMapObject);
            }
            unset($weakMapObject);
            unset($value);
        }
        foreach ($weakReferenceListToReset as $weakReference) {
            $object = $weakReference->get();
            if (!$object) {
                continue;
            }
            $object->_resetState();
            unset($object);
            unset($weakReference);
        }
        /* Note: We must force garbage collection to clean up cyclic referenced objects after _resetState()
        Otherwise, they may still show up in the WeakMap. */
        gc_collect_cycles();
    }

    /**
     * Returns the WeakMap that stores the CollectedObject
     *
     * @return WeakMap
     */
    public function getWeakMap() : WeakMap
    {
        return $this->weakMap;
    }
}
