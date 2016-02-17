<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator\Strategy;

use Zend\Stdlib\Exception\InvalidArgumentException;

/**
 * This Strategy extracts and hydrates int and string values to Boolean values
 *
 * @package Zend\Stdlib\Hydrator\Strategy
 */
final class BooleanStrategy implements StrategyInterface
{
    /**
     * @var int|string
     */
    private $trueValue;

    /**
     * @var int|string
     */
    private $falseValue;

    /**
     * @param int|string $trueValue
     * @param int|string $falseValue
     * @throws InvalidArgumentException
     */
    public function __construct($trueValue, $falseValue)
    {
        if (!is_int($trueValue) && !is_string($trueValue)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to instantiate BooleanStrategy. Expected int or string as $trueValue. %s was given',
                is_object($trueValue) ? get_class($trueValue) : gettype($trueValue)
            ));
        }

        if (!is_int($falseValue) && !is_string($falseValue)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to instantiate BooleanStrategy. Expected int or string as $falseValue. %s was given',
                is_object($falseValue) ? get_class($falseValue) : gettype($falseValue)
            ));
        }

        $this->trueValue  = $trueValue;
        $this->falseValue = $falseValue;
    }

    /**
     * Converts the given value so that it can be extracted by the hydrator.
     *
     * @param  bool $value The original value.
     * @throws InvalidArgumentException
     * @return int|string Returns the value that should be extracted.
     */
    public function extract($value)
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to extract. Expected bool. %s was given.',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        return $value === true ? $this->trueValue : $this->falseValue;
    }

    /**
     * Converts the given value so that it can be hydrated by the hydrator.
     *
     * @param  int|string $value The original value.
     * @throws InvalidArgumentException
     * @return bool Returns the value that should be hydrated.
     */
    public function hydrate($value)
    {
        if (!is_string($value) && !is_int($value)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to hydrate. Expected string or int. %s was given.',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        if ($value === $this->trueValue) {
            return true;
        }

        if ($value === $this->falseValue) {
            return false;
        }

        throw new InvalidArgumentException(sprintf(
            'Unexpected value %s can\'t be hydrated. Expect %s or %s as Value.',
            $value,
            $this->trueValue,
            $this->falseValue
        ));
    }
}
