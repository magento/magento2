<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Element;

use Zend\Validator\InArray as InArrayValidator;

class Radio extends MultiCheckbox
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'radio'
    );

    /**
     * Get validator
     *
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator && !$this->disableInArrayValidator()) {
            $this->validator = new InArrayValidator(array(
                'haystack'  => $this->getValueOptionsValues(),
                'strict'    => false,
            ));
        }
        return $this->validator;
    }
}
