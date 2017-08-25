<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Reader;

/**
 * Class \Magento\Framework\Code\Reader\SourceArgumentsReader
 *
 */
class SourceArgumentsReader
{
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
    public function getConstructorArgumentTypes(\ReflectionClass $class, $inherited = false)
    {
        $output = [null];
        if (!$class->getFileName() || false == $class->hasMethod(
            '__construct'
        ) || !$inherited && $class->getConstructor()->class !== $class->getName()
        ) {
            return $output;
        }
        $reflectionConstructor = $class->getConstructor();
        $fileContent = file($class->getFileName());
        $availableNamespaces = $this->namespaceResolver->getImportedNamespaces($fileContent);
        $availableNamespaces[0] = $class->getNamespaceName();
        $constructorStartLine = $reflectionConstructor->getStartLine() - 1;
        $constructorEndLine = $reflectionConstructor->getEndLine();
        $fileContent = array_slice($fileContent, $constructorStartLine, $constructorEndLine - $constructorStartLine);
        $source = '<?php ' . trim(implode('', $fileContent));

        // Remove parameter default value.
        $source = preg_replace("/ = (.*)/", ',)', $source);

        $methodTokenized = token_get_all($source);
        $argumentsStart = array_search('(', $methodTokenized) + 1;
        $argumentsEnd = array_search(')', $methodTokenized);
        $arguments = array_slice($methodTokenized, $argumentsStart, $argumentsEnd - $argumentsStart);
        foreach ($arguments as &$argument) {
            is_array($argument) ?: $argument = [1 => $argument];
        }
        unset($argument);
        $arguments = array_filter($arguments, function ($token) {
            $blacklist = [T_VARIABLE, T_WHITESPACE];
            if (isset($token[0]) && in_array($token[0], $blacklist)) {
                return false;
            }
            return true;
        });
        $arguments = array_map(function ($element) {
            return $element[1];
        }, $arguments);
        $arguments = array_values($arguments);
        $arguments = implode('', $arguments);
        if (empty($arguments)) {
            return $output;
        }
        $arguments = explode(',', $arguments);
        foreach ($arguments as $key => &$argument) {
            $argument = $this->removeToken($argument, '=');
            $argument = $this->removeToken($argument, '&');
            $argument = $this->namespaceResolver->resolveNamespace($argument, $availableNamespaces);
        }
        unset($argument);
        return $arguments;
    }

    /**
     * Perform namespace resolution if required and return fully qualified name.
     *
     * @param string $argument
     * @param array $availableNamespaces
     * @return string
     * @deprecated 100.2.0
     * @see \Magento\Framework\Code\Reader\NamespaceResolver::resolveNamespace
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
     * @deprecated 100.2.0
     * @see \Magento\Framework\Code\Reader\NamespaceResolver::getImportedNamespaces
     */
    protected function getImportedNamespaces(array $file)
    {
        return $this->namespaceResolver->getImportedNamespaces($file);
    }
}
