<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Factory;

class Compiled extends AbstractFactory
{
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
        $type = $this->config->getInstanceType($requestedType);
        $args = $this->config->getArguments($requestedType);
        if ($args === null) {
            return new $type();
        }

        foreach ($args as $key => &$argument) {
            if (isset($arguments[$key])) {
                $argument = $arguments[$key];
            } elseif (isset($argument['_i_'])) {
                $argument = $this->objectManager->get($argument['_i_']);
            } elseif (isset($argument['_ins_'])) {
                $argument = $this->objectManager->create($argument['_ins_']);
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
        if (substr($type, -12) == '\Interceptor') {
            $args = array_merge([
                $this->objectManager, $this->objectManager->get('Magento\Framework\Interception\PluginListInterface'),
                $this->objectManager->get('Magento\Framework\Interception\ChainInterface'),
            ], $args);
        }

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
                    $argument = $this->objectManager->get($argument['_i_']);
                } elseif (isset($argument['_ins_'])) {
                    $argument = $this->objectManager->create($argument['_ins_']);
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
}
