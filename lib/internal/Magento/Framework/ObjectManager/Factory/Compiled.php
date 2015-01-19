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
        $requestedType = ltrim($requestedType, '\\');
        $args = $this->config->getArguments($requestedType);
        if ($args == null) {
            return new $type();
        }

        foreach ($args as $key => &$argument) {
            if (isset($arguments[$key])) {
                $argument = $arguments[$key];
            } else {
                if ($argument === (array)$argument) {
                    if (isset($argument['__val__']) || array_key_exists('__val__', $argument)) {
                        $argument = $argument['__val__'];
                        if ($argument === (array)$argument) {
                            $this->parseArray($argument);
                        }
                    } elseif (isset($argument['__non_shared__'])) {
                        $argument = $this->objectManager->create($argument['__instance__']);
                    } elseif (isset($argument['__arg__'])) {
                        if (isset($this->globalArguments[$argument['__arg__']])) {
                            $argument = $this->globalArguments[$argument['__arg__']];
                        } else {
                            $argument = $argument['__default__'];
                        }
                    }
                } else {
                    $argument = $this->objectManager->get($argument);
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
}
