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
     * Argument pattern used for configuration
     *
     * @var array
     */
    private $argumentPattern = [
        '_i_' => null,
        '_s_' => false,
        '_v_' => null,
        '_a_' => null,
        '_d_' => null
    ];

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
            $argument = $this->getNonObjectArgument(null);
            if (!$constructorArgument->isRequired()) {
                $argument = $this->getNonObjectArgument($constructorArgument->getDefaultValue());
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
            $argument = $this->getInstanceArgument($configuredArgument['instance']);
            $argument['_s_'] = isset($configuredArgument['shared']) ? $configuredArgument['shared'] : $argument['_s_'];
            return $argument;
        } elseif (isset($configuredArgument['argument'])) {
            return $this->getGlobalArgument($configuredArgument['argument'], $constructorArgument->getDefaultValue());
        }

        return $this->getNonObjectArgument($configuredArgument);
    }

    private function getConfiguredArrayAttribute($array)
    {
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if (isset($value['instance'])) {
                $array[$key] = $this->getInstanceArgument($value['instance']);
                $array[$key]['_s_'] = isset($value['shared']) ? $value['shared'] : $array[$key]['_s_'];
                continue;
            }

            if (isset($value['argument'])) {
                $array[$key] = $this->getGlobalArgument($value['argument'], null);
                continue;
            }

            $array[$key] = $this->getConfiguredArrayAttribute($value);
        }

        return $array;
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
        $argument = $this->argumentPattern;
        $argument['_i_'] = $instanceType;
        $argument['_s_'] = $this->diContainerConfig->isShared($instanceType);
        return $argument;
    }

    /**
     * Returns non object argument
     *
     * @param mixed $value
     * @return array
     */
    private function getNonObjectArgument($value)
    {
        $argument = $this->argumentPattern;
        if (is_array($value)) {
            $value = $this->getConfiguredArrayAttribute($value);
        }
        $argument['_v_'] = $value;
        return $argument;
    }

    /**
     * Returns global argument
     *
     * @param string $value
     * @param string $default
     * @return array
     */
    private function getGlobalArgument($value, $default)
    {
        $argument = $this->argumentPattern;
        $argument['_a_'] = $value;
        $argument['_d_'] = $default;
        return $argument;
    }
}
