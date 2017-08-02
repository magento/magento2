<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Rule\Model\ConditionFactory
 *
 * @since 2.0.0
 */
class ConditionFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * Store all used condition models
     *
     * @var array
     * @since 2.0.0
     */
    private $conditionModels = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($type)
    {
        if (!array_key_exists($type, $this->conditionModels)) {
            $this->conditionModels[$type] = $this->objectManager->create($type);
        }

        return clone $this->conditionModels[$type];
    }
}
