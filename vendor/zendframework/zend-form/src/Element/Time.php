<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Element;

use DateInterval;
use Zend\Validator\DateStep as DateStepValidator;

class Time extends DateTime
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'time',
    );

    /**
     * Default date format
     * @var string
     */
    protected $format = 'H:i:s';

    /**
     * Retrieves a DateStepValidator configured for a Date Input type
     *
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getStepValidator()
    {
        $format    = $this->getFormat();
        $stepValue = (isset($this->attributes['step']))
                     ? $this->attributes['step'] : 60; // Seconds

        $baseValue = (isset($this->attributes['min']))
                     ? $this->attributes['min'] : date($format, 0);

        return new DateStepValidator(array(
            'format'    => $format,
            'baseValue' => $baseValue,
            'step'      => new DateInterval("PT{$stepValue}S"),
        ));
    }
}
