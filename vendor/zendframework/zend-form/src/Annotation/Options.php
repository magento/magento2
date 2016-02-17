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
 * Options annotation
 *
 * Allows passing element, fieldset, or form options to the form factory.
 * Options are used to alter the behavior of the object they address.
 *
 * The value should be an associative array.
 *
 * @Annotation
 */
class Options extends AbstractArrayAnnotation
{
    /**
     * Retrieve the options
     *
     * @return null|array
     */
    public function getOptions()
    {
        return $this->value;
    }
}
