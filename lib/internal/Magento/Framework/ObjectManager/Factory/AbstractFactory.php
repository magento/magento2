<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Factory;

use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class AbstractFactory
 */
abstract class AbstractFactory implements \Magento\Framework\ObjectManager\FactoryInterface
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
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
     * Object creation stack
     *
     * @var array
     */
    protected $creationStack = [];

    /**
     * @param \Magento\Framework\ObjectManager\ConfigInterface     $config
     * @param ObjectManagerInterface                               $objectManager
     * @param \Magento\Framework\ObjectManager\DefinitionInterface $definitions
     * @param array                                                $globalArguments
     */
    public function __construct(
        \Magento\Framework\ObjectManager\ConfigInterface $config,
        ObjectManagerInterface $objectManager = null,
        \Magento\Framework\ObjectManager\DefinitionInterface $definitions = null,
        $globalArguments = []
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->definitions = $definitions ?: $this->getDefinitions();
        $this->globalArguments = $globalArguments;
    }

    /**
     * Set object manager
     *
     * @param ObjectManagerInterface $objectManager
     *
     * @return void
     */
    public function setObjectManager(ObjectManagerInterface $objectManager)
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
     * Get definitions
     *
     * @return \Magento\Framework\ObjectManager\DefinitionInterface
     */
    public function getDefinitions()
    {
        if ($this->definitions === null) {
            $this->definitions = new \Magento\Framework\ObjectManager\Definition\Runtime();
        }
        return $this->definitions;
    }

    /**
     * Create object
     *
     * @param string $type
     * @param array  $args
     *
     * @return object
     * @throws RuntimeException
     */
    protected function createObject($type, $args)
    {
        try {
            return new $type(...array_values($args));
        } catch (\TypeError $exception) {
            /**
             * @var LoggerInterface $logger
             */
            $logger = ObjectManager::getInstance()->get(LoggerInterface::class);
            $logger->critical(
                sprintf('Type Error occurred when creating object: %s, %s', $type, $exception->getMessage())
            );

            throw new RuntimeException(
                new Phrase('Type Error occurred when creating object: %type, %msg', [
                    'type' => $type,
                    'msg' => $exception->getMessage()
                ])
            );
        }
    }

    /**
     * Resolve an argument
     *
     * @param array  $argument
     * @param string $paramType
     * @param mixed  $paramDefault
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
            if (!isset($argument['instance']) || $argument !== (array)$argument) {
                throw new \UnexpectedValueException(
                    'Invalid parameter configuration provided for $' . $paramName . ' argument of ' . $requestedType
                );
            }
            $argumentType = $argument['instance'];

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
        } elseif ($argument === (array)$argument) {
            if (isset($argument['argument'])) {
                if (isset($this->globalArguments[$argument['argument']])) {
                    $argument = $this->globalArguments[$argument['argument']];
                } else {
                    $argument = $paramDefault;
                }
            } elseif (!empty($argument)) {
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

    /**
     * Resolve constructor arguments
     *
     * @param string $requestedType
     * @param array  $parameters
     * @param array  $arguments
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     */
    protected function resolveArgumentsInRuntime($requestedType, array $parameters, array $arguments = [])
    {
        $resolvedArguments = [];
        foreach ($parameters as $parameter) {
            $resolvedArguments[] = $this->getResolvedArgument((string)$requestedType, $parameter, $arguments);
        }

        return array_merge([], ...$resolvedArguments);
    }

    /**
     * Get resolved argument from parameter
     *
     * @param  string $requestedType
     * @param  array  $parameter
     * @param  array  $arguments
     * @return array
     */
    private function getResolvedArgument(string $requestedType, array $parameter, array $arguments): array
    {
        list($paramName, $paramType, $paramRequired, $paramDefault, $isVariadic) = $parameter;
        $argument = null;
        if (!empty($arguments) && (isset($arguments[$paramName]) || array_key_exists($paramName, $arguments))) {
            $argument = $arguments[$paramName];
        } elseif ($paramRequired) {
            if ($paramType) {
                $argument = ['instance' => $paramType];
            } else {
                $this->creationStack = [];
                throw new \BadMethodCallException(
                    'Missing required argument $' . $paramName . ' of ' . $requestedType . '.'
                );
            }
        } else {
            $argument = $paramDefault;
        }

        if ($isVariadic) {
            $variadicArguments = is_array($argument) ? $argument : [$argument];

            foreach ($variadicArguments as &$variadicArgument) {
                $this->resolveArgument($variadicArgument, $paramType, $paramDefault, $paramName, $requestedType);
            };

            return $variadicArguments;
        }

        $this->resolveArgument($argument, $paramType, $paramDefault, $paramName, $requestedType);
        return [$argument];
    }
}
