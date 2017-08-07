<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Condition;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for composite.
 * @since 2.2.0
 */
class ConditionFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.2.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $elementVisibilityConditions
     *
     * @return Condition
     * @since 2.2.0
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
