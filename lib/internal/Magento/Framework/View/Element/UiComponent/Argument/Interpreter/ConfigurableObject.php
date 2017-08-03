<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Argument\Interpreter;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Class ConfigurableObject
 * @since 2.0.0
 */
class ConfigurableObject implements InterpreterInterface
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var InterpreterInterface
     * @since 2.0.0
     */
    protected $argumentInterpreter;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param InterpreterInterface $argumentInterpreter
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager, InterpreterInterface $argumentInterpreter)
    {
        $this->objectManager = $objectManager;
        $this->argumentInterpreter = $argumentInterpreter;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function evaluate(array $data)
    {
        if (isset($data['value'])) {
            $className = $data['value'];
            $arguments = [];
        } else {
            if (!isset($data['argument'])) {
                throw new \InvalidArgumentException('Node "argument" required for this type.');
            }
            foreach ($data['argument'] as $name => $argument) {
                $arguments[$name] = $this->argumentInterpreter->evaluate($argument);
            }
            if (!isset($arguments['class'])) {
                throw new \InvalidArgumentException('Node "argument" with name "class" is required for this type.');
            }
            $className = $arguments['class'];
            unset($arguments['class']);
        }

        return $this->objectManager->create($className, $arguments);
    }
}
