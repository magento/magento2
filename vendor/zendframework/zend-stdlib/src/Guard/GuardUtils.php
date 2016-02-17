<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Guard;

use Traversable;

/**
 * Static guard helper class
 *
 * Bridges the gap for allowing refactoring until traits can be used by default.
 *
 * @deprecated
 */
abstract class GuardUtils
{
    const DEFAULT_EXCEPTION_CLASS = 'Zend\Stdlib\Exception\InvalidArgumentException';

    /**
     * Verifies that the data is an array or Traversable
     *
     * @param  mixed  $data           the data to verify
     * @param  string $dataName       the data name
     * @param  string $exceptionClass FQCN for the exception
     * @throws \Exception
     */
    public static function guardForArrayOrTraversable(
        $data,
        $dataName = 'Argument',
        $exceptionClass = self::DEFAULT_EXCEPTION_CLASS
    ) {
        if (!is_array($data) && !($data instanceof Traversable)) {
            $message = sprintf(
                '%s must be an array or Traversable, [%s] given',
                $dataName,
                is_object($data) ? get_class($data) : gettype($data)
            );
            throw new $exceptionClass($message);
        }
    }

    /**
     * Verify that the data is not empty
     *
     * @param  mixed  $data           the data to verify
     * @param  string $dataName       the data name
     * @param  string $exceptionClass FQCN for the exception
     * @throws \Exception
     */
    public static function guardAgainstEmpty(
        $data,
        $dataName = 'Argument',
        $exceptionClass = self::DEFAULT_EXCEPTION_CLASS
    ) {
        if (empty($data)) {
            $message = sprintf('%s cannot be empty', $dataName);
            throw new $exceptionClass($message);
        }
    }

    /**
     * Verify that the data is not null
     *
     * @param  mixed  $data           the data to verify
     * @param  string $dataName       the data name
     * @param  string $exceptionClass FQCN for the exception
     * @throws \Exception
     */
    public static function guardAgainstNull(
        $data,
        $dataName = 'Argument',
        $exceptionClass = self::DEFAULT_EXCEPTION_CLASS
    ) {
        if (null === $data) {
            $message = sprintf('%s cannot be null', $dataName);
            throw new $exceptionClass($message);
        }
    }
}
