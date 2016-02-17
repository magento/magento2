<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Dom
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Transform CSS selectors to XPath
 *
 * @package    Zend_Dom
 * @subpackage Query
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Dom_Query_Css2Xpath
{
    /**
     * Transform CSS expression to XPath
     *
     * @param  string $path
     * @return string
     */
    public static function transform($path)
    {
        $path = (string) $path;
        if (strstr($path, ',')) {
            $paths       = explode(',', $path);
            $expressions = array();
            foreach ($paths as $path) {
                $xpath = self::transform(trim($path));
                if (is_string($xpath)) {
                    $expressions[] = $xpath;
                } elseif (is_array($xpath)) {
                    $expressions = array_merge($expressions, $xpath);
                }
            }
            return implode('|', $expressions);
        }

        $paths    = array('//');
        $path     = preg_replace('|\s+>\s+|', '>', $path);
        $segments = preg_split('/\s+/', $path);
        foreach ($segments as $key => $segment) {
            $pathSegment = self::_tokenize($segment);
            if (0 == $key) {
                if (0 === strpos($pathSegment, '[contains(')) {
                    $paths[0] .= '*' . ltrim($pathSegment, '*');
                } else {
                    $paths[0] .= $pathSegment;
                }
                continue;
            }
            if (0 === strpos($pathSegment, '[contains(')) {
                foreach ($paths as $key => $xpath) {
                    $paths[$key] .= '//*' . ltrim($pathSegment, '*');
                    $paths[]      = $xpath . $pathSegment;
                }
            } else {
                foreach ($paths as $key => $xpath) {
                    $paths[$key] .= '//' . $pathSegment;
                }
            }
        }

        if (1 == count($paths)) {
            return $paths[0];
        }
        return implode('|', $paths);
    }

    /**
     * Tokenize CSS expressions to XPath
     *
     * @param  string $expression
     * @return string
     */
    protected static function _tokenize($expression)
    {
        // Child selectors
        $expression = str_replace('>', '/', $expression);

        // IDs
        $expression = preg_replace('|#([a-z][a-z0-9_-]*)|i', '[@id=\'$1\']', $expression);
        $expression = preg_replace('|(?<![a-z0-9_-])(\[@id=)|i', '*$1', $expression);

        // arbitrary attribute strict equality
        $expression = preg_replace_callback(
            '|\[([a-z0-9_-]+)=[\'"]([^\'"]+)[\'"]\]|i',
            array(__CLASS__, '_createEqualityExpression'),
            $expression
        );

        // arbitrary attribute contains full word
        $expression = preg_replace_callback(
            '|\[([a-z0-9_-]+)~=[\'"]([^\'"]+)[\'"]\]|i',
            array(__CLASS__, '_normalizeSpaceAttribute'),
            $expression
        );

        // arbitrary attribute contains specified content
        $expression = preg_replace_callback(
            '|\[([a-z0-9_-]+)\*=[\'"]([^\'"]+)[\'"]\]|i',
            array(__CLASS__, '_createContainsExpression'),
            $expression
        );

        // Classes
        $expression = preg_replace(
            '|\.([a-z][a-z0-9_-]*)|i',
            "[contains(concat(' ', normalize-space(@class), ' '), ' \$1 ')]",
            $expression
        );

        /** ZF-9764 -- remove double asterix */
        $expression = str_replace('**', '*', $expression);

        return $expression;
    }

    /**
     * Callback for creating equality expressions
     *
     * @param  array $matches
     * @return string
     */
    protected static function _createEqualityExpression($matches)
    {
        return '[@' . strtolower($matches[1]) . "='" . $matches[2] . "']";
    }

    /**
     * Callback for creating expressions to match one or more attribute values
     *
     * @param  array $matches
     * @return string
     */
    protected static function _normalizeSpaceAttribute($matches)
    {
        return "[contains(concat(' ', normalize-space(@" . strtolower($matches[1]) . "), ' '), ' "
             . $matches[2] . " ')]";
    }

    /**
     * Callback for creating a strict "contains" expression
     *
     * @param  array $matches
     * @return string
     */
    protected static function _createContainsExpression($matches)
    {
        return "[contains(@" . strtolower($matches[1]) . ", '"
             . $matches[2] . "')]";
    }
}
