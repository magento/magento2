<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\Form\Annotation;

/**
 * ValidationGroup annotation
 *
 * Allows passing validation group to the form
 *
 * The value should be an associative array.
 *
 * @Annotation
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ValidationGroup extends AbstractArrayAnnotation
{
    /**
     * Retrieve the options
     *
     * @return null|array
     */
    public function getValidationGroup()
    {
        return $this->value;
    }
}
