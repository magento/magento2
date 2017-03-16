<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout;

/**
 * Composite condition which iterate over included conditions.
 */
class AndCondition
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
            if (!$condition->isVisible($arguments)) {
                return false;
            }
        }

        return true;
    }
}
