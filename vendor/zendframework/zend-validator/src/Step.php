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

class Step extends AbstractValidator
{
    const INVALID = 'typeInvalid';
    const NOT_STEP = 'stepInvalid';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID => "Invalid value given. Scalar expected",
        self::NOT_STEP => "The input is not a valid step"
    );

    /**
     * @var mixed
     */
    protected $baseValue = 0;

    /**
     * @var mixed
     */
    protected $step = 1;

    /**
     * Set default options for this instance
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        } elseif (!is_array($options)) {
            $options = func_get_args();
            $temp['baseValue'] = array_shift($options);
            if (!empty($options)) {
                $temp['step'] = array_shift($options);
            }

            $options = $temp;
        }

        if (isset($options['baseValue'])) {
            $this->setBaseValue($options['baseValue']);
        }
        if (isset($options['step'])) {
            $this->setStep($options['step']);
        }

        parent::__construct($options);
    }

    /**
     * Sets the base value from which the step should be computed
     *
     * @param mixed $baseValue
     * @return Step
     */
    public function setBaseValue($baseValue)
    {
        $this->baseValue = $baseValue;
        return $this;
    }

    /**
     * Returns the base value from which the step should be computed
     *
     * @return string
     */
    public function getBaseValue()
    {
        return $this->baseValue;
    }

    /**
     * Sets the step value
     *
     * @param mixed $step
     * @return Step
     */
    public function setStep($step)
    {
        $this->step = (float) $step;
        return $this;
    }

    /**
     * Returns the step value
     *
     * @return string
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns true if $value is a scalar and a valid step value
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_numeric($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        $fmod = $this->fmod($value - $this->baseValue, $this->step);

        if ($fmod !== 0.0 && $fmod !== $this->step) {
            $this->error(self::NOT_STEP);
            return false;
        }

        return true;
    }

    /**
     * replaces the internal fmod function which give wrong results on many cases
     *
     * @param float $x
     * @param float $y
     * @return float
     */
    protected function fmod($x, $y)
    {
        if ($y == 0.0) {
            return 1.0;
        }

        //find the maximum precision from both input params to give accurate results
        $xFloatSegment = substr($x, strpos($x, '.') + 1) ?: '';
        $yFloatSegment = substr($y, strpos($y, '.') + 1) ?: '';
        $precision = strlen($xFloatSegment) + strlen($yFloatSegment);

        return round($x - $y * floor($x / $y), $precision);
    }
}
