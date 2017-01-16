<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\InputException;

/**
 * Class ConditionPool
 */
class ConditionPool
{
    /**
     * @var ConditionInterface[]
     */
    private $conditions;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ConditionPool constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ConditionInterface[] $conditions
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $conditions = []
    ) {
        $this->conditions = $conditions;
        $this->objectManager = $objectManager;
    }

    /**
     * Returns condition by name
     *
     * @param string $name
     * @return ConditionInterface
     * @throws InputException
     */
    public function getCondition($name)
    {
        if (!isset($this->conditions[$name])) {
            throw new InputException(__('Cannot apply unknown condition'));
        }
        return $this->objectManager->get($this->conditions[$name]);
    }
}
