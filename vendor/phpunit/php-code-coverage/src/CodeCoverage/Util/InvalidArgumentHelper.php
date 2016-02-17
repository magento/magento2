<?php
/*
 * This file is part of the PHP_CodeCoverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Factory for PHP_CodeCoverage_Exception objects that are used to describe
 * invalid arguments passed to a function or method.
 *
 * @since Class available since Release 1.2.0
 */
class PHP_CodeCoverage_Util_InvalidArgumentHelper
{
    /**
     * @param int    $argument
     * @param string $type
     * @param mixed  $value
     */
    public static function factory($argument, $type, $value = null)
    {
        $stack = debug_backtrace(false);

        return new PHP_CodeCoverage_Exception(
            sprintf(
                'Argument #%d%sof %s::%s() must be a %s',
                $argument,
                $value !== null ? ' (' . gettype($value) . '#' . $value . ')' : ' (No Value) ',
                $stack[1]['class'],
                $stack[1]['function'],
                $type
            )
        );
    }
}
