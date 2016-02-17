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
 * ContinueIfEmpty annotation
 *
 * Presence of this annotation is a hint that the associated
 * \Zend\InputFilter\Input should enable the continueIfEmpty flag.
 *
 * @Annotation
 * @deprecated 2.4.8 Use `@Validator({"name":"NotEmpty"})` instead.
 */
class ContinueIfEmpty
{
    /**
     * @var bool
     */
    protected $continueIfEmpty = true;

    /**
     * Receive and process the contents of an annotation
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $continueIfEmpty = (isset($data['value']))
            ? $data['value']
            : false;

        if (! is_bool($continueIfEmpty)) {
            $filter = new BooleanFilter();
            $continueIfEmpty = $filter->filter($continueIfEmpty);
        }

        $this->continueIfEmpty = $continueIfEmpty;
    }

    /**
     * Get value of required flag
     *
     * @return bool
     */
    public function getContinueIfEmpty()
    {
        return $this->continueIfEmpty;
    }
}
