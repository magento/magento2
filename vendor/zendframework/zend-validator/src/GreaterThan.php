<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class GreaterThan extends AbstractValidator
{
    const NOT_GREATER           = 'notGreaterThan';
    const NOT_GREATER_INCLUSIVE = 'notGreaterThanInclusive';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_GREATER => "The input is not greater than '%min%'",
        self::NOT_GREATER_INCLUSIVE => "The input is not greater or equal than '%min%'"
    );

    /**
     * @var array
     */
    protected $messageVariables = array(
        'min' => 'min'
    );

    /**
     * Minimum value
     *
     * @var mixed
     */
    protected $min;

    /**
     * Whether to do inclusive comparisons, allowing equivalence to max
     *
     * If false, then strict comparisons are done, and the value may equal
     * the min option
     *
     * @var bool
     */
    protected $inclusive;

    /**
     * Sets validator options
     *
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (!is_array($options)) {
            $options = func_get_args();
            $temp['min'] = array_shift($options);

            if (!empty($options)) {
                $temp['inclusive'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!array_key_exists('min', $options)) {
            throw new Exception\InvalidArgumentException("Missing option 'min'");
        }

        if (!array_key_exists('inclusive', $options)) {
            $options['inclusive'] = false;
        }

        $this->setMin($options['min'])
             ->setInclusive($options['inclusive']);

        parent::__construct($options);
    }

    /**
     * Returns the min option
     *
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Sets the min option
     *
     * @param  mixed $min
     * @return GreaterThan Provides a fluent interface
     */
    public function setMin($min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Returns the inclusive option
     *
     * @return bool
     */
    public function getInclusive()
    {
        return $this->inclusive;
    }

    /**
     * Sets the inclusive option
     *
     * @param  bool $inclusive
     * @return GreaterThan Provides a fluent interface
     */
    public function setInclusive($inclusive)
    {
        $this->inclusive = $inclusive;
        return $this;
    }

    /**
     * Returns true if and only if $value is greater than min option
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        if ($this->inclusive) {
            if ($this->min > $value) {
                $this->error(self::NOT_GREATER_INCLUSIVE);
                return false;
            }
        } else {
            if ($this->min >= $value) {
                $this->error(self::NOT_GREATER);
                return false;
            }
        }

        return true;
    }
}
