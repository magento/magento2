<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Factory\Dynamic;

class Production extends \Magento\Framework\ObjectManager\Factory\AbstractFactory
{
    /**
     * Resolve constructor arguments
     *
     * @param string $requestedType
     * @param array $parameters
     * @param array $arguments
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     */
    protected function _resolveArguments($requestedType, array $parameters, array $arguments = [])
    {
        $resolvedArguments = [];
        $arguments = count($arguments)
            ? array_replace($this->config->getArguments($requestedType), $arguments)
            : $this->config->getArguments($requestedType);
        foreach ($parameters as $parameter) {
            list($paramName, $paramType, $paramRequired, $paramDefault) = $parameter;
            $argument = null;
            if (!empty($arguments) && (isset($arguments[$paramName]) || array_key_exists($paramName, $arguments))) {
                $argument = $arguments[$paramName];
            } elseif ($paramRequired) {
                $argument = ['instance' => $paramType];
            } else {
                $argument = $paramDefault;
            }

            $this->resolveArgument($argument, $paramType, $paramDefault, $paramName, $requestedType);

            $resolvedArguments[] = $argument;
        }
        return $resolvedArguments;
    }

    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     *
     * @return object
     *
     * @throws \Exception
     */
    public function create($requestedType, array $arguments = [])
    {
        $type = $this->config->getInstanceType($requestedType);
        $parameters = $this->definitions->getParameters($type);
        if ($parameters == null) {
            return new $type();
        }
        $args = $this->_resolveArguments($requestedType, $parameters, $arguments);

        return $this->createObject($type, $args);
    }
}
