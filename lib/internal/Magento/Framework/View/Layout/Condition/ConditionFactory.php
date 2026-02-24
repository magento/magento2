<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Layout\Condition;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for composite.
 */
class ConditionFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $elementVisibilityConditions
     *
     * @return Condition
     */
    public function create(array $elementVisibilityConditions)
    {
        $conditions = [];
        foreach ($elementVisibilityConditions as $condition) {
            $conditions[] = $this->objectManager->create($condition['name']);
        }
        return $this->objectManager->create(Condition::class, ['conditions' => $conditions]);
    }
}
