<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Element;

use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Regex as RegexValidator;

class Color extends Element implements InputProviderInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'color',
    );

    /**
     * @var \Zend\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * Get validator
     *
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new RegexValidator('/^#[0-9a-fA-F]{6}$/');
        }
        return $this->validator;
    }

    /**
     * Provide default input rules for this element
     *
     * Attaches a color validator.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        return array(
            'name' => $this->getName(),
            'required' => true,
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
                array('name' => 'Zend\Filter\StringToLower'),
            ),
            'validators' => array(
                $this->getValidator(),
            ),
        );
    }
}
