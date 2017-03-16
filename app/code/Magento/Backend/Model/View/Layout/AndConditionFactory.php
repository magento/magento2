<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for composite.
 */
class AndConditionFactory
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ConditionFactoryInterface[]
     */
    private $conditionFactories;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConditionFactoryInterface[] $conditionFactories
     */
    public function __construct(ObjectManagerInterface $objectManager, array $conditionFactories)
    {
        $this->objectManager = $objectManager;
        $this->conditionFactories = $conditionFactories;
    }

    /**
     * @param array $elementAttributes
     *
     * @return AndCondition
     */
    public function create(array $elementAttributes)
    {
        $conditions = [];
        foreach ($this->conditionFactories as $conditionName => $conditionFactory) {
            if (array_key_exists($conditionName, $elementAttributes)) {
                $conditions[] = $conditionFactory->create($elementAttributes[$conditionName]);
            }
        }
        return $this->objectManager->create(AndCondition::class, ['conditions' => $conditions]);
    }
}
