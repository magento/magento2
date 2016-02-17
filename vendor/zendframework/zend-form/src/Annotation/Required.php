<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Annotation;

use Zend\Filter\Boolean as BooleanFilter;

/**
 * Required annotation
 *
 * Use this annotation to specify the value of the "required" flag for a given
 * input. Since the flag defaults to "true", this will typically be used to
 * "unset" the flag (e.g., "@Annotation\Required(false)"). Any boolean value
 * understood by \Zend\Filter\Boolean is allowed as the content.
 *
 * @Annotation
 */
class Required
{
    /**
     * @var bool
     */
    protected $required = true;

    /**
     * Receive and process the contents of an annotation
     *
     * @param  array $data
     */
    public function __construct(array $data)
    {
        if (!isset($data['value'])) {
            $data['value'] = false;
        }

        $required = $data['value'];

        if (!is_bool($required)) {
            $filter   = new BooleanFilter();
            $required = $filter->filter($required);
        }

        $this->required = $required;
    }

    /**
     * Get value of required flag
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }
}
