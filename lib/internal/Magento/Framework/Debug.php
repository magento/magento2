<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework;

/**
 * Magento Debug methods
 */
class Debug
{
    /**
     * @var int
     */
    public static $argLength = 16;

    /**
     * Magento Root path
     *
     * @var string
     */
    protected static $_filePath;

    /**
     * Retrieve real root path with last directory separator
     *
     * @return string
     */
    public static function getRootPath()
    {
        if (self::$_filePath === null) {
            if (defined('BP')) {
                self::$_filePath = BP;
            } else {
                self::$_filePath = dirname(__DIR__);
            }
        }
        return self::$_filePath;
    }

    /**
     * Formats the code location by generating a relative file path and line number.
     *
     * This method processes an input array that contains file path and line number information.
     * It removes the root path from the file path (if present) to create a relative path
     * and formats the result as a string in the format "relative/path/to/file.php:line".
     *
     * Example Input:
     * [
     *     'file' => '/var/www/magento2/app/code/Magento/Test/Block/Test.php',
     *     'line' => 1
     * ]
     *
     * Example Output:
     * "app/code/Magento/Test/Block/Test.php:1"
     *
     * @param array|null $data An associative array containing:
     *                         - 'file': The absolute file path (string).
     *                         - 'line': The line number (int, optional).
     * @return string A formatted string representing the relative file path and line number,
     *                or an empty string if the 'file' key is not present.
     */
    public static function normalizeCodeLocation(?array $data): string
    {
        $fileName = '';

        // Check if 'file' exists in the data array
        if (isset($data['file'])) {
            // Determine the position of the root path in the file path
            $pos = \strpos($data['file'], self::getRootPath());

            // If Magento root path is part of the file path, trim it to create a relative path
            if (false !== $pos) {
                $data['file'] = \substr(
                    $data['file'],
                    \strlen(self::getRootPath()) + 1
                );
            }

            // Format the file path and line number into the desired string format
            $fileName = \sprintf('%s:%d', $data['file'], $data['line'] ?? 0);
        }

        return $fileName;
    }

    /**
     * Prints or returns a backtrace
     *
     * @param bool $return      return or print
     * @param bool $html        output in HTML format
     * @param bool $withArgs    add short arguments of methods
     * @return string|bool
     */
    public static function backtrace($return = false, $html = true, $withArgs = true)
    {
        $trace = debug_backtrace();
        return self::trace($trace, $return, $html, $withArgs);
    }

    /**
     * Prints or return a trace
     *
     * @param array $trace       trace array
     * @param bool  $return      return or print
     * @param bool  $html        output in HTML format
     * @param bool  $withArgs    add short arguments of methods
     * @return string|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function trace(array $trace, $return = false, $html = true, $withArgs = true)
    {
        $out = '';
        if ($html) {
            $out .= '<pre>';
        }

        foreach ($trace as $i => $data) {
            // skip self
            if ($i == 0) {
                continue;
            }

            // prepare method arguments
            $args = [];
            if (isset($data['args']) && $withArgs) {
                foreach ($data['args'] as $arg) {
                    $args[] = self::_formatCalledArgument($arg);
                }
            }

            // Fix static test: 'Variable $methodName might not be defined.'
            $methodName = '';
            
            // prepare method's name
            if (isset($data['class']) && isset($data['function'])) {
                if (isset($data['object']) && get_class($data['object']) != $data['class']) {
                    $className = get_class($data['object']) . '[' . $data['class'] . ']';
                } else {
                    $className = $data['class'];
                }
                if (isset($data['object'])) {
                    $className .= sprintf('#%s#', spl_object_hash($data['object']));
                }

                $methodName = sprintf(
                    '%s%s%s(%s)',
                    $className,
                    isset($data['type']) ? $data['type'] : '->',
                    $data['function'],
                    join(', ', $args)
                );
            } elseif (isset($data['function'])) {
                $methodName = sprintf('%s(%s)', $data['function'], join(', ', $args));
            }

            $fileName = self::normalizeCodeLocation($data);

            if ($fileName) {
                $out .= sprintf('#%d %s called at [%s]', $i, $methodName, $fileName);
            } else {
                $out .= sprintf('#%d %s', $i, $methodName);
            }

            $out .= "\n";
        }

        if ($html) {
            $out .= '</pre>';
        }

        if ($return) {
            return $out;
        } else {
            // Fix static test: 'Use of echo language construct is discouraged.'
            // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
            echo $out;
            return true;
        }
    }

    /**
     * Format argument in called method
     *
     * @param mixed $arg
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected static function _formatCalledArgument($arg)
    {
        $out = '';
        if (is_object($arg)) {
            $out .= sprintf("&%s#%s#", get_class($arg), spl_object_hash($arg));
        } elseif (is_resource($arg)) {
            $out .= '#[' . get_resource_type($arg) . ']';
        } elseif (is_array($arg)) {
            $isAssociative = false;
            $args = [];
            foreach ($arg as $k => $v) {
                if (!is_numeric($k)) {
                    $isAssociative = true;
                }
                $args[$k] = self::_formatCalledArgument($v);
            }
            if ($isAssociative) {
                $arr = [];
                foreach ($args as $k => $v) {
                    $arr[] = self::_formatCalledArgument($k) . ' => ' . $v;
                }
                $out .= 'array(' . join(', ', $arr) . ')';
            } else {
                $out .= 'array(' . join(', ', $args) . ')';
            }
        } elseif ($arg === null) {
            $out .= 'NULL';
        } elseif (is_numeric($arg) || is_float($arg)) {
            $out .= $arg;
        } elseif (is_string($arg)) {
            if (strlen($arg) > self::$argLength) {
                $arg = substr($arg, 0, self::$argLength) . "...";
            }
            $arg = strtr($arg, ["\t" => '\t', "\r" => '\r', "\n" => '\n', "'" => '\\\'']);
            $out .= "'" . $arg . "'";
        } elseif (is_bool($arg)) {
            $out .= $arg === true ? 'true' : 'false';
        }

        return $out;
    }
}
