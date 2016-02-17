<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form;

use Traversable;
use Zend\InputFilter\InputFilterProviderInterface;

class InputFilterProviderFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * Holds the specification which will be returned by getInputFilterSpecification
     *
     * @var array|Traversable
     */
    protected $filterSpec = array();

    /**
     * @return array|Traversable
     */
    public function getInputFilterSpecification()
    {
        return $this->filterSpec;
    }

    /**
     * @param array|Traversable $filterSpec
     */
    public function setInputFilterSpecification($filterSpec)
    {
        $this->filterSpec = $filterSpec;
    }

    /**
     * Set options for a fieldset. Accepted options are:
     * - input_filter_spec: specification to be returned by getInputFilterSpecification
     *
     * @param  array|Traversable $options
     * @return Element|ElementInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($options['input_filter_spec'])) {
            $this->setInputFilterSpecification($options['input_filter_spec']);
        }

        return $this;
    }
}
