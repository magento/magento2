<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib;

/**
 * Utility methods for the boolean data type
 *
 * @api
 */
class BooleanUtils
{
    /**
     * Expressions that mean boolean TRUE
     *
     * @var array
     */
    private $trueValues;

    /**
     * Expressions that mean boolean FALSE
     *
     * @var array
     */
    private $falseValues;

    /**
     * @param array $trueValues
     * @param array $falseValues
     * @codingStandardsIgnoreStart
     */
    public function __construct(
        array $trueValues = [true, 1, 'true', '1'],
        array $falseValues = [false, 0, 'false', '0']
    ) {
        $this->trueValues = $trueValues;
        $this->falseValues = $falseValues;
    }

    // @codingStandardsIgnoreEnd

    /**
     * Retrieve boolean value for an expression
     *
     * @param mixed $value Boolean expression
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function toBoolean($value)
    {
        /**
         * Built-in function filter_var() is not used, because such values as on/off are irrelevant in some contexts
         * @link http://www.php.net/manual/en/filter.filters.validate.php
         */
        if (in_array($value, $this->trueValues, true)) {
            return true;
        }
        if (in_array($value, $this->falseValues, true)) {
            return false;
        }
        $allowedValues = array_merge($this->trueValues, $this->falseValues);
        throw new \InvalidArgumentException(
            'Boolean value is expected, supported values: ' . var_export($allowedValues, true)
        );
    }

    /**
     * Try to convert $value to boolean else return non processed $value
     *
     * @param mixed $value
     * @return mixed
     */
    public function convert($value)
    {
        if (in_array($value, $this->trueValues, true)) {
            return true;
        } elseif (in_array($value, $this->falseValues, true)) {
            return false;
        } else {
            return $value;
        }
    }
}
