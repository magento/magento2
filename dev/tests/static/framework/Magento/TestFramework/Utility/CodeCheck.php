<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Utility;

/**
 * Searches for usage of classes, namespaces, functions, etc in PHP files
 */
class CodeCheck
{
    /**
     * Check if the class is used in the content
     *
     * @param string $className
     * @param string $content
     * @return bool
     */
    public static function isClassUsed($className, $content)
    {
        /* avoid matching namespace instead of class */
        $content = preg_replace('/namespace[^;]+;/', '', $content);
        return self::_isRegexpMatched('/[^a-z\d_\$]' . preg_quote($className, '/') . '[^a-z\d_\\\\]/iS', $content);
    }

    /**
     * Check if the namespace is used in the content
     *
     * @param string $namespace
     * @param string $content
     * @return bool
     */
    public static function isNamespaceUsed($namespace, $content)
    {
        return self::_isRegexpMatched('/namespace\s+' . preg_quote($namespace, '/') . ';/S', $content)
        || self::_isRegexpMatched('/[^a-zA-Z\d_]' . preg_quote($namespace . '\\', '/') . '/S', $content);
    }

    /**
     * Check if the specified function is called in the content.
     * Note that declarations are not considered.
     *
     * If class context is not specified, invocation of all functions or methods (of any class)
     * will be matched across the board
     *
     * If some class is specified, only its methods will be matched as follows:
     * - usage of class::method
     * - usage of $this, self and static within the class and its descendants
     *
     * @param string $method
     * @param string $content
     * @param string $class
     * @return bool
     */
    public static function isFunctionCalled($method, $content, $class = null)
    {
        $quotedMethod = preg_quote($method, '/');
        if (!$class) {
            return self::_isRegexpMatched(
                '/(?<![a-z\d_:]|->|function\s)' . $quotedMethod . '\s*\(/iS',
                $content
            );
        }
        // without opening parentheses to match static callbacks notation
        if (self::_isRegexpMatched(
            '/' . preg_quote($class, '/') . '::\s*' . $quotedMethod . '[^a-z\d_]/iS',
            $content
        )
        ) {
            return true;
        }
        if (self::isClassOrInterface($content, $class) || self::isDirectDescendant($content, $class)) {
            return self::_isRegexpMatched('/this->' . $quotedMethod . '\s*\(/iS', $content)
            || self::_isRegexpMatched(
                '/(self|static|parent)::\s*' . $quotedMethod . '\s*\(/iS',
                $content
            );
        }
    }

    /**
     * Check if methods or functions are used in the content
     *
     * If class context is specified, only the class and its descendants will be checked.
     *
     * @param string $property
     * @param string $content
     * @param string $class
     * @return bool
     */
    public static function isPropertyUsed($property, $content, $class = null)
    {
        if ($class) {
            if (!self::isClassOrInterface($content, $class) && !self::isDirectDescendant($content, $class)) {
                return false;
            }
        }
        return self::_isRegexpMatched(
            '/[^a-z\d_]' . preg_quote($property, '/') . '[^a-z\d_]/iS',
            $content
        );
    }

    /**
     * Analyze content to determine whether it is a declaration of the specified class/interface
     *
     * @param string $content
     * @param string $name
     * @return bool
     */
    public static function isClassOrInterface($content, $name)
    {
        return self::_isRegexpMatched('/\b(?:class|interface)\s+' . preg_quote($name, '/') . '\b[^{]*\{/iS', $content);
    }

    /**
     * Analyze content to determine whether this is a direct descendant of the specified class/interface
     *
     * @param string $content
     * @param string $name
     * @return bool
     */
    public static function isDirectDescendant($content, $name)
    {
        $name = preg_quote($name, '/');
        return self::_isRegexpMatched(
            '/\s+extends\s+\\\\?' . $name . '\b|\s+implements\s+[^{]*\b' . $name . '\b[^{^\\\\]*\{/iS',
            $content
        );
    }

    /**
     * Check if content matches the regexp
     *
     * @param string $regexp
     * @param string $content
     * @throws \Exception
     * @return bool True if the content matches the regexp
     */
    protected static function _isRegexpMatched($regexp, $content)
    {
        $result = preg_match($regexp, $content);
        if ($result === false) {
            throw new \Exception('An error occurred when matching regexp "' . $regexp . '""');
        }
        return 1 === $result;
    }
}
