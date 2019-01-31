<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Condition;

/**
 * Composite condition which iterate over included conditions.
 */
class Condition
{
    /**
     * @var VisibilityConditionInterface[]
     */
    private $conditions;

    /**
     * @param VisibilityConditionInterface[] $conditions
     */
    public function __construct(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * Validate logical condition for ui component
     * If validation passed block will be displayed
     *
     * @param array $arguments Attributes from element node.
     *
     * @return bool
     */
    public function isVisible(array $arguments)
    {
        foreach ($this->conditions as $condition) {
            if (!$condition->isVisible($arguments[$condition->getName()]['arguments'])) {
                return false;
            }
        }

        return true;
    }
}
