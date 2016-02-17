<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Element;

use Zend\Validator\DateStep as DateStepValidator;
use Zend\Validator\Regex as RegexValidator;

class Week extends DateTime
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'week',
    );

    /**
     * Retrieves a Date Validator configured for a Week Input type
     *
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getDateValidator()
    {
        return new RegexValidator('/^[0-9]{4}\-W[0-9]{2}$/');
    }

    /**
     * Retrieves a DateStep Validator configured for a Week Input type
     *
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getStepValidator()
    {
        $stepValue = (isset($this->attributes['step']))
                     ? $this->attributes['step'] : 1; // Weeks

        $baseValue = (isset($this->attributes['min']))
                     ? $this->attributes['min'] : '1970-W01';

        return new DateStepValidator(array(
            'format'    => 'Y-\WW',
            'baseValue' => $baseValue,
            'step'      => new \DateInterval("P{$stepValue}W"),
        ));
    }
}
