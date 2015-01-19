<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Factory;

abstract class AbstractFactory implements \Magento\Framework\ObjectManager\FactoryInterface
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Object manager config
     *
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     */
    protected $config;

    /**
     * Definition list
     *
     * @var \Magento\Framework\ObjectManager\DefinitionInterface
     */
    protected $definitions;

    /**
     * Global arguments
     *
     * @var array
     */
    protected $globalArguments;

    /**
     * @param \Magento\Framework\ObjectManager\ConfigInterface $config
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\ObjectManager\DefinitionInterface $definitions
     * @param array $globalArguments
     */
    public function __construct(
        \Magento\Framework\ObjectManager\ConfigInterface $config,
        \Magento\Framework\ObjectManagerInterface $objectManager = null,
        \Magento\Framework\ObjectManager\DefinitionInterface $definitions = null,
        $globalArguments = []
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->definitions = $definitions ?: new \Magento\Framework\ObjectManager\Definition\Runtime();
        $this->globalArguments = $globalArguments;
    }

    /**
     * Set object manager
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     *
     * @return void
     */
    public function setObjectManager(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Set global arguments
     *
     * @param array $arguments
     *
     * @return void
     */
    public function setArguments($arguments)
    {
        $this->globalArguments = $arguments;
    }

    /**
     * Create object
     *
     * @param string $type
     * @param array $args
     *
     * @return object
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     */
    protected function createObject($type, $args)
    {
        switch (count($args)) {
            case 1:
                return new $type($args[0]);
            case 2:
                return new $type($args[0], $args[1]);
            case 3:
                return new $type($args[0], $args[1], $args[2]);
            case 4:
                return new $type($args[0], $args[1], $args[2], $args[3]);
            case 5:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4]);
            case 6:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
            case 7:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
            case 8:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
            case 9:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]
                );
            case 10:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9]
                );
            case 11:
                return new $type(
                    $args[0],
                    $args[1],
                    $args[2],
                    $args[3],
                    $args[4],
                    $args[5],
                    $args[6],
                    $args[7],
                    $args[8],
                    $args[9],
                    $args[10]
                );
            case 12:
                return new $type(
                    $args[0],
                    $args[1],
                    $args[2],
                    $args[3],
                    $args[4],
                    $args[5],
                    $args[6],
                    $args[7],
                    $args[8],
                    $args[9],
                    $args[10],
                    $args[11]
                );
            case 13:
                return new $type(
                    $args[0],
                    $args[1],
                    $args[2],
                    $args[3],
                    $args[4],
                    $args[5],
                    $args[6],
                    $args[7],
                    $args[8],
                    $args[9],
                    $args[10],
                    $args[11],
                    $args[12]
                );
            case 14:
                return new $type(
                    $args[0],
                    $args[1],
                    $args[2],
                    $args[3],
                    $args[4],
                    $args[5],
                    $args[6],
                    $args[7],
                    $args[8],
                    $args[9],
                    $args[10],
                    $args[11],
                    $args[12],
                    $args[13]
                );
            case 15:
                return new $type(
                    $args[0],
                    $args[1],
                    $args[2],
                    $args[3],
                    $args[4],
                    $args[5],
                    $args[6],
                    $args[7],
                    $args[8],
                    $args[9],
                    $args[10],
                    $args[11],
                    $args[12],
                    $args[13],
                    $args[14]
                );
            default:
                $reflection = new \ReflectionClass($type);
                return $reflection->newInstanceArgs($args);
        }
    }

    /**
     * Resolve an argument
     *
     * @param array &$argument
     * @param string $paramType
     * @param mixed $paramDefault
     * @param string $paramName
     * @param string $requestedType
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function resolveArgument(&$argument, $paramType, $paramDefault, $paramName, $requestedType)
    {
        if ($paramType && $argument !== $paramDefault && !is_object($argument)) {
            $argumentType = $argument['instance'];
            if (!isset($argument['instance']) || $argument !== (array)$argument) {
                throw new \UnexpectedValueException(
                    'Invalid parameter configuration provided for $' . $paramName . ' argument of ' . $requestedType
                );
            }

            if (isset($argument['shared'])) {
                $isShared = $argument['shared'];
            } else {
                $isShared = $this->config->isShared($argumentType);
            }

            if ($isShared) {
                $argument = $this->objectManager->get($argumentType);
            } else {
                $argument = $this->objectManager->create($argumentType);
            }

        } else if ($argument === (array)$argument) {
            if (isset($argument['argument'])) {
                if (isset($this->globalArguments[$argument['argument']])) {
                    $argument = $this->globalArguments[$argument['argument']];
                } else {
                    $argument = $paramDefault;
                }
            } else if (!empty($argument)) {
                $this->parseArray($argument);
            }
        }
    }

    /**
     * Parse array argument
     *
     * @param array $array
     *
     * @return void
     */
    protected function parseArray(&$array)
    {
        foreach ($array as $key => $item) {
            if ($item === (array)$item) {
                if (isset($item['instance'])) {
                    if (isset($item['shared'])) {
                        $isShared = $item['shared'];
                    } else {
                        $isShared = $this->config->isShared($item['instance']);
                    }

                    if ($isShared) {
                        $array[$key] = $this->objectManager->get($item['instance']);
                    } else {
                        $array[$key] = $this->objectManager->create($item['instance']);
                    }

                } elseif (isset($item['argument'])) {
                    if (isset($this->globalArguments[$item['argument']])) {
                        $array[$key] = $this->globalArguments[$item['argument']];
                    } else {
                        $array[$key] = null;
                    }
                } else {
                    $this->parseArray($array[$key]);
                }
            }
        }
    }
}
