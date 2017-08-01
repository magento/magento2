<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Reader;

/**
 * Class resolve short namespaces to fully qualified namespaces.
 * @since 2.2.0
 */
class NamespaceResolver
{
    /**
     * Namespace separator
     */
    const NS_SEPARATOR = '\\';

    /**
     * @var ScalarTypesProvider
     * @since 2.2.0
     */
    private $scalarTypesProvider;

    /**
     * @var array
     * @since 2.2.0
     */
    private $namespaces = [];

    /**
     * NamespaceResolver constructor.
     * @param ScalarTypesProvider $scalarTypesProvider
     * @since 2.2.0
     */
    public function __construct(ScalarTypesProvider $scalarTypesProvider = null)
    {
        $this->scalarTypesProvider = $scalarTypesProvider ?: new ScalarTypesProvider();
    }

    /**
     * Perform namespace resolution if required and return fully qualified name.
     *
     * @param string $type
     * @param array $availableNamespaces
     * @return string
     * @since 2.2.0
     */
    public function resolveNamespace($type, array $availableNamespaces)
    {
        if (substr($type, 0, 1) !== self::NS_SEPARATOR
            && !in_array($type, $this->scalarTypesProvider->getTypes())
            && !empty($type)
        ) {
            $name = explode(self::NS_SEPARATOR, $type);
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
                return self::NS_SEPARATOR . $availableNamespaces[0] . self::NS_SEPARATOR . $type;
            }
        }
        return $type;
    }

    /**
     * Get all imported namespaces from provided class.
     *
     * @param array $fileContent
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.2.0
     */
    public function getImportedNamespaces(array $fileContent)
    {
        $fileContent = implode('', $fileContent);

        $cacheKey = sha1($fileContent);

        if (isset($this->namespaces[$cacheKey])) {
            return $this->namespaces[$cacheKey];
        }

        $fileContent = token_get_all($fileContent);
        $classStart = array_search('{', $fileContent);
        $fileContent = array_slice($fileContent, 0, $classStart);
        $output = [];
        foreach ($fileContent as $position => $token) {
            if (is_array($token) && $token[0] === T_USE) {
                $import = array_slice($fileContent, $position);
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
        $this->namespaces[$cacheKey] = $output;
        return $this->namespaces[$cacheKey];
    }
}
