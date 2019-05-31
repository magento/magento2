<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element\UiComponent\Argument\Interpreter;

use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Class ConfigurableObject
 */
class ConfigurableObject implements InterpreterInterface
{
    /**
     * @var array
     */
    private $classWhitelist = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var InterpreterInterface
     */
    protected $argumentInterpreter;

    /**
     * @var ConfigInterface
     */
    private $objectManagerConfig;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param InterpreterInterface $argumentInterpreter
     * @param array $classWhitelist
     * @param ConfigInterface|null $objectManagerConfig
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        InterpreterInterface $argumentInterpreter,
        array $classWhitelist = [],
        ConfigInterface $objectManagerConfig = null
    ) {
        $this->objectManager = $objectManager;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->classWhitelist = $classWhitelist;
        $this->objectManagerConfig = $objectManagerConfig ?? $objectManager->get(ConfigInterface::class);
    }

    /**
     * @inheritdoc
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

            $type = $this->objectManagerConfig->getInstanceType(
                $this->objectManagerConfig->getPreference($arguments['class'])
            );

            $classIsAllowed = false;
            foreach ($this->classWhitelist as $allowedClass) {
                if (is_subclass_of($type, $allowedClass, true)) {
                    $classIsAllowed = true;
                    break;
                }
            }

            if (!$classIsAllowed) {
                throw new \InvalidArgumentException(
                    sprintf('Class argument is invalid: %s', $arguments['class'])
                );
            }

            $className = $arguments['class'];
            unset($arguments['class']);
        }

        return $this->objectManager->create($className, $arguments);
    }
}
