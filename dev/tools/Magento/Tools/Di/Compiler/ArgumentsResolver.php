<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\Di\Compiler;

class ArgumentsResolver
{
    /**
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     */
    private $diContainerConfig;

    /**
     * @param \Magento\Framework\ObjectManager\ConfigInterface $diContainerConfig
     */
    public function __construct(\Magento\Framework\ObjectManager\ConfigInterface $diContainerConfig)
    {
        $this->diContainerConfig = $diContainerConfig;
    }

    /**
     * Returns resolved constructor arguments for given instance type
     *
     * @param string $instanceType
     * @param ConstructorArgument[] $constructor
     * @return array|null
     */
    public function getResolvedConstructorArguments($instanceType, $constructor)
    {
        if (!$constructor) {
            return null;
        }
        $configuredArguments = $this->getConfiguredArguments($instanceType);

        $arguments = [];
        /** @var ConstructorArgument $constructorArgument */
        foreach ($constructor as $constructorArgument) {
            $argument = self::getNonObjectArgument(null);
            if (!$constructorArgument->isRequired()) {
                $argument = self::getNonObjectArgument($constructorArgument->getDefaultValue());
            } elseif ($constructorArgument->getType()) {
                $argument = $this->getInstanceArgument($constructorArgument->getType());
            }

            if (isset($configuredArguments[$constructorArgument->getName()])) {
                $argument = $this->getConfiguredArgument(
                    $configuredArguments[$constructorArgument->getName()],
                    $constructorArgument
                );
            }
            $arguments[$constructorArgument->getName()] = $argument;
        }
        return $arguments;
    }

    /**
     * Returns formatted configured argument
     *
     * @param array $configuredArgument
     * @param ConstructorArgument $constructorArgument
     * @return mixed
     */
    private function getConfiguredArgument($configuredArgument, ConstructorArgument $constructorArgument)
    {
        if ($constructorArgument->getType()) {
            return $this->getInstanceArgument($configuredArgument['instance']);
        } elseif (isset($configuredArgument['argument'])) {
            return self::getGlobalArgument($configuredArgument['argument'], $constructorArgument->getDefaultValue());
        }

        return self::getNonObjectArgument($configuredArgument);
    }

    /**
     * Return configured arguments
     *
     * @param string $instanceType
     * @return array
     */
    private function getConfiguredArguments($instanceType)
    {
        $configuredArguments = $this->diContainerConfig->getArguments($instanceType);
        return array_map(
            function ($type) {
                if (isset($type['instance'])) {
                    $type['instance'] = ltrim($type['instance'], '\\');
                }

                return $type;
            },
            $configuredArguments
        );
    }

    /**
     * Returns instance argument
     *
     * @param string $instanceType
     * @return array|mixed
     */
    private function getInstanceArgument($instanceType)
    {
        return $this->diContainerConfig->isShared($instanceType)
            ? $instanceType
            : self::getNonSharedInstance($instanceType);
    }

    /**
     * Returns argument of non shared instance
     *
     * @param string $instanceType
     * @return array
     */
    private static function getNonSharedInstance($instanceType)
    {
        return [
            '__non_shared__' => true,
            '__instance__' => $instanceType
        ];
    }

    /**
     * Returns non object argument
     *
     * @param mixed $value
     * @return array
     */
    private static function getNonObjectArgument($value)
    {
        return ['__val__' => $value];
    }

    /**
     * Returns global argument
     *
     * @param string $argument
     * @param string $default
     * @return array
     */
    private static function getGlobalArgument($argument, $default)
    {
        return [
            '__arg__' => $argument,
            '__default__' => $default
        ];
    }
}
