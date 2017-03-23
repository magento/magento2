<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Argument\Interpreter;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Class ConfigurableObject
 */
class ConfigurableObject implements InterpreterInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var InterpreterInterface
     */
    protected $argumentInterpreter;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param InterpreterInterface $argumentInterpreter
     */
    public function __construct(ObjectManagerInterface $objectManager, InterpreterInterface $argumentInterpreter)
    {
        $this->objectManager = $objectManager;
        $this->argumentInterpreter = $argumentInterpreter;
    }

    /**
     * {@inheritdoc}
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
