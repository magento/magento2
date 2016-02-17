<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Annotation;

use Zend\Form\Exception;

abstract class AbstractStringAnnotation
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Receive and process the contents of an annotation
     *
     * @param  array $data
     * @throws Exception\DomainException if a 'value' key is missing, or its value is not a string
     */
    public function __construct(array $data)
    {
        if (!isset($data['value']) || !is_string($data['value'])) {
            throw new Exception\DomainException(sprintf(
                '%s expects the annotation to define a string; received "%s"',
                get_class($this),
                gettype($data['value'])
            ));
        }
        $this->value = $data['value'];
    }
}
