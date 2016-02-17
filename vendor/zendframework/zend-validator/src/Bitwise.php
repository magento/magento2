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

class Bitwise extends AbstractValidator
{
    const OP_AND = 'and';
    const OP_XOR = 'xor';

    const NOT_AND        = 'notAnd';
    const NOT_AND_STRICT = 'notAndStrict';
    const NOT_XOR        = 'notXor';

    /**
     * @var integer
     */
    protected $control;

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_AND        => "The input has no common bit set with '%control%'",
        self::NOT_AND_STRICT => "The input doesn't have the same bits set as '%control%'",
        self::NOT_XOR        => "The input has common bit set with '%control%'",
    );

    /**
     * Additional variables available for validation failure messages
     *
     * @var array
     */
    protected $messageVariables = array(
        'control' => 'control',
    );

    /**
     * @var integer
     */
    protected $operator;

    /**
     * @var boolean
     */
    protected $strict = false;

    /**
     * Sets validator options
     * Accepts the following option keys:
     *   'control'  => integer
     *   'operator' =>
     *   'strict'   => boolean
     *
     * @param array|Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        }

        if (!is_array($options)) {
            $options = func_get_args();

            $temp['control'] = array_shift($options);

            if (!empty($options)) {
                $temp['operator'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['strict'] = array_shift($options);
            }

            $options = $temp;
        }

        parent::__construct($options);
    }

    /**
     * Returns the control parameter.
     *
     * @return integer
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * Returns the operator parameter.
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Returns the strict parameter.
     *
     * @return boolean
     */
    public function getStrict()
    {
        return $this->strict;
    }

    /**
     * Returns true if and only if $value is between min and max options, inclusively
     * if inclusive option is true.
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        if (self::OP_AND === $this->operator) {
            if ($this->strict) {
                // All the bits set in value must be set in control
                $this->error(self::NOT_AND_STRICT);

                return (bool) (($this->control & $value) == $value);
            } else {
                // At least one of the bits must be common between value and control
                $this->error(self::NOT_AND);

                return (bool) ($this->control & $value);
            }
        } elseif (self::OP_XOR === $this->operator) {
            $this->error(self::NOT_XOR);

            return (bool) (($this->control ^ $value) === ($this->control | $value));
        }

        return false;
    }

    /**
     * Sets the control parameter.
     *
     * @param  integer $control
     * @return Bitwise
     */
    public function setControl($control)
    {
        $this->control = (int) $control;

        return $this;
    }

    /**
     * Sets the operator parameter.
     *
     * @param  string  $operator
     * @return Bitwise
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Sets the strict parameter.
     *
     * @param  boolean $strict
     * @return Bitwise
     */
    public function setStrict($strict)
    {
        $this->strict = (bool) $strict;

        return $this;
    }
}
