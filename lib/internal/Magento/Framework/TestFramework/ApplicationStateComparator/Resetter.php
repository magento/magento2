<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\ApplicationStateComparator;

use Magento\Framework\ObjectManager\Resetter\Resetter as OriginalResetter;
use Magento\Framework\ObjectManager\Resetter\WeakMapSorter;
use Magento\Framework\ObjectManagerInterface;
use WeakMap;

/**
 * Resetter that also tracks state for StateMonitor
 */
class Resetter extends OriginalResetter
{
    /** @var WeakMap instances to be reset after request */
    private WeakMap $collectedWeakMap;

    /**
     * @var Collector
     */
    private Collector $collector;

    /**
     * @var SkipListAndFilterList
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly SkipListAndFilterList $skipListAndFilterList;

    /**
     * @var array
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly array $skipList;

    /** @var ObjectManagerInterface */
    private ObjectManagerInterface $objectManager;

    /** @var WeakMapSorter|null */
    private ?WeakMapSorter $weakMapSorter = null;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->collectedWeakMap = new WeakMap;
        $this->skipListAndFilterList =  new SkipListAndFilterList;
        $this->skipList = $this->skipListAndFilterList->getSkipList(
            '*',
            CompareType::COMPARE_CONSTRUCTED_AGAINST_CURRENT
        );
        parent::__construct();
    }

    /**
     * Sets object manager
     *
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function setObjectManager(ObjectManagerInterface $objectManager): void
    {
        $this->collector = new Collector($objectManager, $this->skipListAndFilterList);
        parent::setObjectManager($objectManager);
    }

    /**
     * Add instance to be reset later, and also collect state as it was first constructed.
     *
     * @param object $instance
     * @return void
     */
    public function addInstance(object $instance) : void
    {
        $this->collectedWeakMap[$instance] =
            $this->collector->getPropertiesFromObject($instance, CompareType::COMPARE_CONSTRUCTED_AGAINST_CURRENT);
        parent::addInstance($instance);
    }

    /**
     * Returns the WeakMap that stores the CollectedObject
     *
     * @return WeakMap with CollectedObject as values
     */
    public function getCollectedWeakMap() : WeakMap
    {
        return $this->collectedWeakMap;
    }
}
