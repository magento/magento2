<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Factory;

class Compiled extends AbstractFactory
{
    /**
     * Object manager config
     *
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     */
    protected $config;

    /**
     * Global arguments
     *
     * @var array
     */
    protected $globalArguments;

    /**
     * @var array
     */
    private $sharedInstances;

    /**
     * @param \Magento\Framework\ObjectManager\ConfigInterface $config
     * @param array $sharedInstances
     * @param array $globalArguments
     */
    public function __construct(
        \Magento\Framework\ObjectManager\ConfigInterface $config,
        &$sharedInstances = [],
        $globalArguments = []
    ) {
        $this->config = $config;
        $this->globalArguments = $globalArguments;
        $this->sharedInstances = &$sharedInstances;
    }

    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function create($requestedType, array $arguments = [])
    {
        $args = $this->config->getArguments($requestedType);
        $type = $this->config->getInstanceType($requestedType);

        if (!$args) {
            return new $type();
        }

        foreach ($args as $key => &$argument) {
            if (isset($arguments[$key])) {
                $argument = $arguments[$key];
            } elseif (isset($argument['_i_'])) {
                $argument = $this->get($argument['_i_']);
            } elseif (isset($argument['_ins_'])) {
                $argument = $this->create($argument['_ins_']);
            } elseif (isset($argument['_v_'])) {
                $argument = $argument['_v_'];
            } elseif (isset($argument['_vac_'])) {
                $argument = $argument['_vac_'];
                $this->parseArray($argument);
            } elseif (isset($argument['_vn_'])) {
                $argument = null;
            } elseif (isset($argument['_a_'])) {
                if (isset($this->globalArguments[$argument['_a_']])) {
                    $argument = $this->globalArguments[$argument['_a_']];
                } else {
                    $argument = $argument['_d_'];
                }
            }
        }

        $args = array_values($args);

        return $this->createObject($type, $args);
    }

    /**
     * Parse array argument
     *
     * @param array $array
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function parseArray(&$array)
    {
        foreach ($array as $key => &$argument) {
            if ($argument === (array)$argument) {
                if (isset($argument['_i_'])) {
                    $argument = $this->get($argument['_i_']);
                } elseif (isset($argument['_ins_'])) {
                    $argument = $this->create($argument['_ins_']);
                } elseif (isset($argument['_a_'])) {
                    if (isset($this->globalArguments[$argument['_a_']])) {
                        $argument = $this->globalArguments[$argument['_a_']];
                    } else {
                        $argument = $argument['_d_'];
                    }
                } else {
                    $this->parseArray($argument);
                }
            }
        }
    }

    /**
     * Retrieve cached object instance
     *
     * @param string $type
     * @return mixed
     */
    protected function get($type)
    {
        if (!isset($this->sharedInstances[$type])) {
            $this->sharedInstances[$type] = $this->create($type);
        }
        return $this->sharedInstances[$type];
    }
}
