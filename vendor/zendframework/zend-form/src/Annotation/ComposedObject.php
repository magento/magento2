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
 * ComposedObject annotation
 *
 * Use this annotation to specify another object with annotations to parse
 * which you can then add to the form as a fieldset. The value should be a
 * string indicating the fully qualified class name of the composed object
 * to use.
 *
 * @Annotation
 */
class ComposedObject extends AbstractArrayOrStringAnnotation
{
    /**
     * Retrieve the composed object classname
     *
     * @return null|string
     */
    public function getComposedObject()
    {
        if (is_array($this->value)) {
            return $this->value['target_object'];
        }
        return $this->value;
    }

    /**
     * Is this composed object a collection or not
     *
     * @return bool
     */
    public function isCollection()
    {
        return is_array($this->value) && isset($this->value['is_collection']) && $this->value['is_collection'];
    }

    /**
     * Retrieve the options for the composed object
     *
     * @return array
     */
    public function getOptions()
    {
        return is_array($this->value) && isset($this->value['options']) ? $this->value['options'] : array();
    }
}
