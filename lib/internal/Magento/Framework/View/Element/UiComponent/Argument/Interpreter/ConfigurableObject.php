<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element\UiComponent\Argument\Interpreter;

use Magento\Framework\Code\Reader\ClassReader;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

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
     * @var array
     */
    private $deniedClassList = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var InterpreterInterface
     */
    protected $argumentInterpreter;

    /**
     * @var ClassReader|null
     */
    private $classReader;

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
     * @param ClassReader|null $classReader
     * @param ConfigInterface|null $objectManagerConfig
     * @param array $deniedClassList
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        InterpreterInterface $argumentInterpreter,
        array $classWhitelist = [],
        ClassReader $classReader = null,
        ConfigInterface $objectManagerConfig = null,
        array $deniedClassList = []
    ) {
        $this->objectManager = $objectManager;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->classWhitelist = $classWhitelist;
        $this->deniedClassList = $deniedClassList;
        $this->classReader = $classReader ?? $objectManager->get(ClassReader::class);
        $this->objectManagerConfig = $objectManagerConfig ?? $objectManager->get(ConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function evaluate(array $data)
    {
        $type = null;
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

            $type = $this->objectManagerConfig->getInstanceType(
                $this->objectManagerConfig->getPreference($className)
            );

            $classParents = $this->getParents($type);

            $whitelistIntersection = array_intersect($classParents, $this->classWhitelist);

            if (empty($whitelistIntersection)) {
                throw new \InvalidArgumentException(
                    sprintf('Class argument is invalid: %s', $className)
                );
            }
        }

        if ($type === null) {
            $type = $this->objectManagerConfig->getInstanceType(
                $this->objectManagerConfig->getPreference($className)
            );
            $classParents = array_merge([$type], $this->getParents($type));
        }

        $deniedIntersection = array_intersect($classParents, $this->deniedClassList);

        if (!empty($deniedIntersection)) {
            throw new \InvalidArgumentException(
                sprintf('Class argument is invalid: %s', $className)
            );
        }

        return $this->objectManager->create($className, $arguments);
    }

    /**
     * Retrieves all the parent classes and interfaces for a class including the ones implemented by the class itself
     *
     * @param string $type
     * @return string[]
     */
    private function getParents(string $type)
    {
        $classParents = $this->classReader->getParents($type) ?? [];
        foreach ($classParents as $parent) {
            if (empty($parent)) {
                continue;
            }
            $classParents = array_merge($classParents, $this->getParents($parent));
        }

        return $classParents;
    }
}
