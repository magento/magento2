<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Reader;

class SourceArgumentsReader
{
    /**
     * Namespace separator
     */
    const NS_SEPARATOR = '\\';

    /**
     * Read constructor argument types from source code and perform namespace resolution if required.
     *
     * @param \ReflectionClass $class
     * @param bool $inherited
     * @return array List of constructor argument types.
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
        $availableNamespaces = $this->getImportedNamespaces($fileContent);
        $availableNamespaces[0] = $class->getNamespaceName();
        $constructorStartLine = $reflectionConstructor->getStartLine() - 1;
        $constructorEndLine = $reflectionConstructor->getEndLine();
        $fileContent = array_slice($fileContent, $constructorStartLine, $constructorEndLine - $constructorStartLine);
        $source = '<?php ' . trim(implode('', $fileContent));
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
            $argument = $this->removeDefaultValue($argument);
            $argument = $this->resolveNamespaces($argument, $availableNamespaces);
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
     */
    protected function resolveNamespaces($argument, $availableNamespaces)
    {
        if (substr($argument, 0, 1) !== self::NS_SEPARATOR && $argument !== 'array' && !empty($argument)) {
            $name = explode(self::NS_SEPARATOR, $argument);
            $unqualifiedName = $name[0];
            $isQualifiedName = count($name) > 1 ? true : false;
            if (isset($availableNamespaces[$unqualifiedName])) {
                $namespace = $availableNamespaces[$unqualifiedName];
                if ($isQualifiedName) {
                    array_shift($name);
                    return $namespace . self::NS_SEPARATOR . implode(self::NS_SEPARATOR, $name);
                }
                return $namespace;
            } else {
                return self::NS_SEPARATOR . $availableNamespaces[0] . self::NS_SEPARATOR . $argument;
            }
        }
        return $argument;
    }

    /**
     * Remove default value from argument.
     *
     * @param string $argument
     * @return string
     */
    protected function removeDefaultValue($argument)
    {
        $position = strpos($argument, '=');
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
     */
    protected function getImportedNamespaces(array $file)
    {
        $file = implode('', $file);
        $file = token_get_all($file);
        $classStart = array_search('{', $file);
        $file = array_slice($file, 0, $classStart);
        $output = [];
        foreach ($file as $position => $token) {
            if (is_array($token) && $token[0] === T_USE) {
                $import = array_slice($file, $position);
                $importEnd = array_search(';', $import);
                $import = array_slice($import, 0, $importEnd);
                $imports = [];
                $importsCount = 0;
                foreach ($import as $item) {
                    if ($item === ',') {
                        $importsCount++;
                        continue;
                    }
                    $imports[$importsCount][] = $item;
                }
                foreach ($imports as $import) {
                    $import = array_filter($import, function ($token) {
                        $whitelist = [T_NS_SEPARATOR, T_STRING, T_AS];
                        if (isset($token[0]) && in_array($token[0], $whitelist)) {
                            return true;
                        }
                        return false;
                    });
                    $import = array_map(function ($element) {
                        return $element[1];
                    }, $import);
                    $import = array_values($import);
                    if ($import[0] === self::NS_SEPARATOR) {
                        array_shift($import);
                    }
                    $importName = null;
                    if (in_array('as', $import)) {
                        $importName = array_splice($import, -1)[0];
                        array_pop($import);
                    }
                    $useStatement = implode('', $import);
                    if ($importName) {
                        $output[$importName] = self::NS_SEPARATOR . $useStatement;
                    } else {
                        $key = explode(self::NS_SEPARATOR, $useStatement);
                        $key = end($key);
                        $output[$key] = self::NS_SEPARATOR . $useStatement;
                    }
                }
            }
        }
        return $output;
    }
}
