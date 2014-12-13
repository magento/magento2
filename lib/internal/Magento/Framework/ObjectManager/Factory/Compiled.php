<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
                if (is_array($argument)) {
                    if (array_key_exists('__val__', $argument)) {
                        $argument = $argument['__val__'];
                        if (is_array($argument)) {
                            $this->parseArray($argument);
                        }
                    } elseif (isset($argument['__non_shared__'])) {
                        $argument = $this->objectManager->create($argument['__instance__']);
                    } elseif (isset($argument['__arg__'])) {
                        $argument = isset($this->globalArguments[$argument['__arg__']])
                            ? $this->globalArguments[$argument['__arg__']]
                            : $argument['__default__'];
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
