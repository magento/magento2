<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Rule\Model\Condition\ConditionInterface;

class ConditionFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Store all used condition models
     *
     * @var array
     */
    private $conditionModels = [];

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create new object for each requested model.
     * If model is requested first time, store it at array.
     * It's made by performance reasons to avoid initialization of same models each time when rules are being processed.
     *
     * @param string $type
     *
     * @return \Magento\Rule\Model\Condition\ConditionInterface
     *
     * @throws \LogicException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function create($type)
    {
        if (!array_key_exists($type, $this->conditionModels)) {
            if (!class_exists($type)) {
                throw new \InvalidArgumentException('Class does not exist');
            }
            if (!in_array(ConditionInterface::class, class_implements($type))) {
                throw new \InvalidArgumentException('Class does not implement condition interface');
            }
            $this->conditionModels[$type] = $this->objectManager->create($type);
        }

        return clone $this->conditionModels[$type];
    }
}
