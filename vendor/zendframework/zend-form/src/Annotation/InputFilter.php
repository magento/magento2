<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Annotation;

/**
 * InputFilter annotation
 *
 * Use this annotation to specify a specific input filter class to use with the
 * form. The value should be a string indicating the fully qualified class name
 * of the input filter to use.
 *
 * @Annotation
 */
class InputFilter extends AbstractArrayOrStringAnnotation
{
    /**
     * Retrieve the input filter class
     *
     * @return null|string
     */
    public function getInputFilter()
    {
        return $this->value;
    }
}
