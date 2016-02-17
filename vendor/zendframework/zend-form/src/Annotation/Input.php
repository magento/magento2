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
 * Input annotation
 *
 * Use this annotation to specify a specific input class to use with an element.
 * The value should be a string indicating the fully qualified class name of the
 * input to use.
 *
 * @Annotation
 */
class Input extends AbstractStringAnnotation
{
    /**
     * Retrieve the input class
     *
     * @return null|string
     */
    public function getInput()
    {
        return $this->value;
    }
}
