<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Condition;

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
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $elementVisibilityConditions
     *
     * @return AndCondition
     */
    public function create(array $elementVisibilityConditions)
    {
        $conditions = [];
        foreach ($elementVisibilityConditions as $condition) {
            $conditions[] = $this->objectManager->create($condition['name']);
        }
        return $this->objectManager->create(AndCondition::class, ['conditions' => $conditions]);
    }
}
