<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Reader;

use Magento\Framework\GetParameterClassTrait;

class SourceArgumentsReader
{
    use GetParameterClassTrait;

    /**
     * Namespace separator
     * @deprecated
     * @see \Magento\Framework\Code\Reader\NamespaceResolver::NS_SEPARATOR
     */
    const NS_SEPARATOR = '\\';

    /**
     * @var NamespaceResolver
     */
    private $namespaceResolver;

    /**
     * @param NamespaceResolver|null $namespaceResolver
     */
    public function __construct(NamespaceResolver $namespaceResolver = null)
    {
        $this->namespaceResolver = $namespaceResolver ?: new NamespaceResolver();
    }

    /**
     * Read constructor argument types from source code and perform namespace resolution if required.
     *
     * @param \ReflectionClass $class
     * @param bool $inherited
     * @return array List of constructor argument types.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getConstructorArgumentTypes(
        \ReflectionClass $class,
        $inherited = false
    ) {
        $output = [null];
        if (!$class->getFileName() || false == $class->hasMethod(
            '__construct'
        ) || !$inherited && $class->getConstructor()->class !== $class->getName()
        ) {
            return $output;
        }

        //Reading parameters' types.
        $params = $class->getConstructor()->getParameters();
        /** @var string[] $types */
        $types = [];
        foreach ($params as $param) {
            //For the sake of backward compatibility.
            $typeName = '';
            $parameterType = $param->getType();
            if ($parameterType && $parameterType->getName() === 'array') {
                //For the sake of backward compatibility.
                $typeName = 'array';
            } else {
                try {
                    $paramClass = $this->getParameterClass($param);
                    if ($paramClass) {
                        $typeName = '\\' .$paramClass->getName();
                    }
                } catch (\ReflectionException $exception) {
                    //If there's a problem loading a class then ignore it and
                    //just return it's name.
                    $typeName = '\\' .$parameterType->getName();
                }
            }
            $types[] = $typeName;
        }
        if (!$types) {
            //For the sake of backward compatibility.
            $types = [null];
        }

        return $types;
    }

    /**
     * Perform namespace resolution if required and return fully qualified name.
     *
     * @param string $argument
     * @param array $availableNamespaces
     * @return string
     * @deprecated 101.0.0
     * @see getConstructorArgumentTypes
     */
    protected function resolveNamespaces($argument, $availableNamespaces)
    {
        return $this->namespaceResolver->resolveNamespace($argument, $availableNamespaces);
    }

    /**
     * Remove default value from argument.
     *
     * @param string $argument
     * @param string $token
     * @return string
     *
     * @deprecated 102.0.0 Not used anymore.
     */
    protected function removeToken($argument, $token)
    {
        $position = strpos($argument, $token);
        if (is_numeric($position)) {
            return substr($argument, 0, $position);
        }
        return $argument;
    }

    /**
     * Get all imported namespaces.
     *
     * @param array $file
     * @return array
     * @deprecated 101.0.0
     * @see getConstructorArgumentTypes
     */
    protected function getImportedNamespaces(array $file)
    {
        return $this->namespaceResolver->getImportedNamespaces($file);
    }
}
