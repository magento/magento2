<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\ApplicationStateComparator;

use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManager\Resetter\ResetterInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Dynamic Factory Decorator for State test.  Uses Resetter to reset object and collect properties.
 */
class DynamicFactoryDecorator extends Developer implements ResetAfterRequestInterface
{
    //phpcs:ignore
    private readonly Collector $collector;

    //phpcs:ignore
    private readonly array $skipList;

    /**
     * @var ResetterInterface
     */
    private ResetterInterface $resetter;

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
        $skipListAndFilterList =  new SkipListAndFilterList;
        $this->skipList = $skipListAndFilterList->getSkipList('', CompareType::COMPARE_CONSTRUCTED_AGAINST_CURRENT);
        $this->collector = new Collector($this->objectManager, $skipListAndFilterList);
        $this->objectManager->addSharedInstance($skipListAndFilterList, SkipListAndFilterList::class);
        $this->objectManager->addSharedInstance($this->collector, Collector::class);
        $this->resetter = $objectManager->create(Resetter::class);
    }

    /**
     * @inheritDoc
     */
    public function setObjectManager(ObjectManagerInterface $objectManager)
    {
        parent::setObjectManager($objectManager);
        $this->resetter->setObjectManager($objectManager);
    }

    /**
     * @inheritDoc
     */
    public function create($type, array $arguments = [])
    {
        $object = parent::create($type, $arguments);
        $this->resetter->addInstance($object);
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
        $this->resetter->_resetState();
    }

    /**
     * Gets resetter
     *
     * @return ResetterInterface
     */
    public function getResetter() : ResetterInterface
    {
        return $this->resetter;
    }
}
