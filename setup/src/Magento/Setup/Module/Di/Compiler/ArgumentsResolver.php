<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler;

class ArgumentsResolver
{
    /**
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     */
    private $diContainerConfig;

    /**
     * Shared instance argument pattern used for configuration
     *
     * @var array
     */
    private $sharedInstancePattern = [
        '_i_' => null,
    ];

    /**
     * Instance argument pattern used for configuration
     *
     * @var array
     */
    private $notSharedInstancePattern = [
        '_ins_' => null,
    ];

    /**
     * Value argument pattern used for configuration
     *
     * @var array
     */
    private $valuePattern = [
        '_v_' => null,
    ];

    /**
     * Value null argument pattern used for configuration
     *
     * @var array
     */
    private $nullValuePattern = [
        '_vn_' => true,
    ];

    /**
     * Value configured array argument pattern used for configuration
     *
     * @var array
     */
    private $configuredArrayValuePattern = [
        '_vac_' => true,
    ];

    /**
     * Configured argument pattern used for configuration
     *
     * @var array
     */
    private $configuredPattern = [
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
            $argument = $this->getConfiguredInstanceArgument($configuredArgument);
            return $argument;
        } elseif (isset($configuredArgument['argument'])) {
            return $this->getGlobalArgument($configuredArgument['argument'], $constructorArgument->getDefaultValue());
        }

        return $this->getNonObjectArgument($configuredArgument);
    }

    /**
     * Returns configured array attribute
     *
     * @param array $array
     * @return mixed
     */
    private function getConfiguredArrayAttribute($array)
    {
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if (isset($value['instance'])) {
                $array[$key] = $this->getConfiguredInstanceArgument($value);
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
     * Returns configured instance argument
     *
     * @param array $config
     * @return array|mixed
     */
    private function getConfiguredInstanceArgument(array $config)
    {
        $argument = $this->getInstanceArgument($config['instance']);
        if (isset($config['shared'])) {
            if ($config['shared']) {
                $pattern = $this->sharedInstancePattern;
                $pattern['_i_'] = current($argument);
            } else {
                $pattern = $this->notSharedInstancePattern;
                $pattern['_ins_'] = current($argument);
            }
            $argument = $pattern;
        }
        return $argument;
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
        if ($this->diContainerConfig->isShared($instanceType)) {
            $argument = $this->sharedInstancePattern;
            $argument['_i_'] = $instanceType;
        } else {
            $argument = $this->notSharedInstancePattern;
            $argument['_ins_'] = $instanceType;
        }
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
        if ($value === null) {
            return $this->nullValuePattern;
        }

        $argument = $this->valuePattern;
        if (is_array($value)) {
            if ($this->isConfiguredArray($value)) {
                $value = $this->getConfiguredArrayAttribute($value);
                $argument = $this->configuredArrayValuePattern;
                $argument['_vac_'] = $value;
                return $argument;
            }
        }

        $argument['_v_'] = $value;
        return $argument;
    }

    /**
     * Whether array is configurable
     *
     * @param array $value
     * @return bool
     */
    private function isConfiguredArray($value)
    {
        foreach ($value as $configuredValue) {
            if (!is_array($configuredValue)) {
                continue;
            }

            if (array_key_exists('instance', $configuredValue) || array_key_exists('argument', $configuredValue)) {
                return true;
            }

            if ($this->isConfiguredArray($configuredValue)) {
                return true;
            }
        }

        return false;
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
        $argument = $this->configuredPattern;
        $argument['_a_'] = $value;
        $argument['_d_'] = $default;
        return $argument;
    }
}
