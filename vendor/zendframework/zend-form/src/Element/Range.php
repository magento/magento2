<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Element;

use Zend\Form\Element\Number as NumberElement;
use Zend\I18n\Validator\IsFloat as NumberValidator;
use Zend\Validator\GreaterThan as GreaterThanValidator;
use Zend\Validator\LessThan as LessThanValidator;
use Zend\Validator\Step as StepValidator;

class Range extends NumberElement
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'range',
    );

    /**
     * Get validator
     *
     * @return \Zend\Validator\ValidatorInterface[]
     */
    protected function getValidators()
    {
        if ($this->validators) {
            return $this->validators;
        }

        $validators = array();
        $validators[] = new NumberValidator();

        $inclusive = true;
        if (!empty($this->attributes['inclusive'])) {
            $inclusive = $this->attributes['inclusive'];
        }

        $validators[] = new GreaterThanValidator(array(
            'min'       => (isset($this->attributes['min'])) ? $this->attributes['min'] : 0,
            'inclusive' => $inclusive
        ));

        $validators[] = new LessThanValidator(array(
            'max'       => (isset($this->attributes['max'])) ? $this->attributes['max'] : 100,
            'inclusive' => $inclusive
        ));

        if (!isset($this->attributes['step'])
            || 'any' !== $this->attributes['step']
        ) {
            $validators[] = new StepValidator(array(
                'baseValue' => (isset($this->attributes['min'])) ? $this->attributes['min'] : 0,
                'step'      => (isset($this->attributes['step'])) ? $this->attributes['step'] : 1,
            ));
        }

        $this->validators = $validators;
        return $this->validators;
    }
}
