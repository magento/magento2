<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Condition;

/**
 * Composite condition which iterate over included conditions.
 * @since 2.2.0
 */
class Condition
{
    /**
     * @var VisibilityConditionInterface[]
     * @since 2.2.0
     */
    private $conditions;

    /**
     * @param VisibilityConditionInterface[] $conditions
     * @since 2.2.0
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
     * @since 2.2.0
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
