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
 * Filter annotation
 *
 * Expects an associative array defining the filter.  Typically, this includes
 * the "name" with an associated string value indicating the filter name or
 * class, and optionally an "options" key with an object/associative array value
 * of options to pass to the filter constructor.
 *
 * This annotation may be specified multiple times; filters will be added
 * to the filter chain in the order specified.
 *
 * @Annotation
 */
class Filter extends AbstractArrayAnnotation
{
    /**
     * Retrieve the filter specification
     *
     * @return null|array
     */
    public function getFilter()
    {
        return $this->value;
    }
}
