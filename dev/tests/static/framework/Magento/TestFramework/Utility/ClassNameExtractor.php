<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

class ClassNameExtractor
{
    /**
     * Get class name with namespace
     *
     * @param string $fileContent
     * @return bool|string
     */
    public function getNameWithNamespace($fileContent)
    {
        $namespace = $this->getNamespace($fileContent);
        $name = $this->getName($fileContent);
        if ($namespace && $name) {
            return $namespace . '\\' . $name;
        }
        return false;
    }

    /**
     * Get class name
     *
     * @param string $fileContent
     * @return string|bool
     */
    public function getName($fileContent)
    {
        $namespace = $this->getNamespace($fileContent);
        if (isset($namespace)) {
            preg_match_all(
                '/^(class|abstract\sclass|interface)\s([a-z0-9]+)(\sextends|\simplements|$)/im',
                $fileContent,
                $classMatches
            );
            if (isset($classMatches[2][0])) {
                return $classMatches[2][0];
            }
        }
        return false;
    }

    /**
     * Get class namespace
     *
     * @param string $fileContent
     * @return string|bool
     */
    public function getNamespace($fileContent)
    {
        preg_match_all(
            '/namespace\s([a-z0-9\\\\]+);/im',
            $fileContent,
            $namespaceMatches
        );
        if (isset($namespaceMatches[1][0])) {
            return $namespaceMatches[1][0];
        }
        return false;
    }
}
