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
use DateTimezone;
use Zend\Form\Element\DateTime as DateTimeElement;
use Zend\Validator\DateStep as DateStepValidator;

class Date extends DateTimeElement
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'date',
    );

    /**
     * Date format to use for DateTime values. By default, this is RFC-3339,
     * full-date (Y-m-d), which is what HTML5 dictates.
     *
     * @var string
     */
    protected $format = 'Y-m-d';

    /**
     * Retrieves a DateStep Validator configured for a Date Input type
     *
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getStepValidator()
    {
        $format    = $this->getFormat();
        $stepValue = (isset($this->attributes['step']))
                     ? $this->attributes['step'] : 1; // Days

        $baseValue = (isset($this->attributes['min']))
                     ? $this->attributes['min'] : date($format, 0);

        return new DateStepValidator(array(
            'format'    => $format,
            'baseValue' => $baseValue,
            'timezone'  => new DateTimezone('UTC'),
            'step'      => new DateInterval("P{$stepValue}D"),
        ));
    }
}
