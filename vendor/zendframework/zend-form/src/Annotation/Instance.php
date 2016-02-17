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
 * Instance (formerly "object") annotation
 *
 * Use this annotation to specify an object instance to use as the bound object
 * of a form or fieldset
 *
 * @Annotation
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Instance extends AbstractStringAnnotation
{
    /**
     * Retrieve the object
     *
     * @return null|string
     */
    public function getObject()
    {
        return $this->value;
    }
}
