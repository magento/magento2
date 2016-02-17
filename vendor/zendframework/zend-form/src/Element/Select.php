<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\Form\Element;

use Traversable;
use Zend\Form\Element;
use Zend\Form\ElementInterface;
use Zend\Form\Exception\InvalidArgumentException;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Explode as ExplodeValidator;
use Zend\Validator\InArray as InArrayValidator;

class Select extends Element implements InputProviderInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'select',
    );

    /**
     * @var \Zend\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * @var bool
     */
    protected $disableInArrayValidator = false;

    /**
     * Create an empty option (option with label but no value). If set to null, no option is created
     *
     * @var bool
     */
    protected $emptyOption = null;

    /**
     * @var array
     */
    protected $valueOptions = array();

    /**
     * @var bool
     */
    protected $useHiddenElement = false;

    /**
     * @var string
     */
    protected $unselectedValue = '';

    /**
     * @return array
     */
    public function getValueOptions()
    {
        return $this->valueOptions;
    }

    /**
     * @param  array $options
     * @return Select
     */
    public function setValueOptions(array $options)
    {
        $this->valueOptions = $options;

        // Update InArrayValidator validator haystack
        if (null !== $this->validator) {
            if ($this->validator instanceof InArrayValidator) {
                $validator = $this->validator;
            }
            if ($this->validator instanceof ExplodeValidator
                && $this->validator->getValidator() instanceof InArrayValidator
            ) {
                $validator = $this->validator->getValidator();
            }
            if (!empty($validator)) {
                $validator->setHaystack($this->getValueOptionsValues());
            }
        }

        return $this;
    }

    /**
     * @param string $key
     * @return self
     */
    public function unsetValueOption($key)
    {
        if (isset($this->valueOptions[$key])) {
            unset($this->valueOptions[$key]);
        }

        return $this;
    }

    /**
     * Set options for an element. Accepted options are:
     * - label: label to associate with the element
     * - label_attributes: attributes to use when the label is rendered
     * - value_options: list of values and labels for the select options
     * _ empty_option: should an empty option be prepended to the options ?
     *
     * @param  array|Traversable $options
     * @return Select|ElementInterface
     * @throws InvalidArgumentException
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($this->options['value_options'])) {
            $this->setValueOptions($this->options['value_options']);
        }
        // Alias for 'value_options'
        if (isset($this->options['options'])) {
            $this->setValueOptions($this->options['options']);
        }

        if (isset($this->options['empty_option'])) {
            $this->setEmptyOption($this->options['empty_option']);
        }

        if (isset($this->options['disable_inarray_validator'])) {
            $this->setDisableInArrayValidator($this->options['disable_inarray_validator']);
        }

        if (isset($options['use_hidden_element'])) {
            $this->setUseHiddenElement($options['use_hidden_element']);
        }

        if (isset($options['unselected_value'])) {
            $this->setUnselectedValue($options['unselected_value']);
        }

        return $this;
    }

    /**
     * Set a single element attribute
     *
     * @param  string $key
     * @param  mixed  $value
     * @return Select|ElementInterface
     */
    public function setAttribute($key, $value)
    {
        // Do not include the options in the list of attributes
        // TODO: Deprecate this
        if ($key === 'options') {
            $this->setValueOptions($value);
            return $this;
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * Set the flag to allow for disabling the automatic addition of an InArray validator.
     *
     * @param bool $disableOption
     * @return Select
     */
    public function setDisableInArrayValidator($disableOption)
    {
        $this->disableInArrayValidator = (bool) $disableOption;
        return $this;
    }

    /**
     * Get the disable in array validator flag.
     *
     * @return bool
     */
    public function disableInArrayValidator()
    {
        return $this->disableInArrayValidator;
    }

    /**
     * Set the string for an empty option (can be empty string). If set to null, no option will be added
     *
     * @param  string|null $emptyOption
     * @return Select
     */
    public function setEmptyOption($emptyOption)
    {
        $this->emptyOption = $emptyOption;
        return $this;
    }

    /**
     * Return the string for the empty option (null if none)
     *
     * @return string|null
     */
    public function getEmptyOption()
    {
        return $this->emptyOption;
    }

    /**
     * Get validator
     *
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator && !$this->disableInArrayValidator()) {
            $validator = new InArrayValidator(array(
                'haystack' => $this->getValueOptionsValues(),
                'strict'   => false
            ));

            if ($this->isMultiple()) {
                $validator = new ExplodeValidator(array(
                    'validator'      => $validator,
                    'valueDelimiter' => null, // skip explode if only one value
                ));
            }

            $this->validator = $validator;
        }
        return $this->validator;
    }

    /**
     * Do we render hidden element?
     *
     * @param  bool $useHiddenElement
     * @return Select
     */
    public function setUseHiddenElement($useHiddenElement)
    {
        $this->useHiddenElement = (bool) $useHiddenElement;
        return $this;
    }

    /**
     * Do we render hidden element?
     *
     * @return bool
     */
    public function useHiddenElement()
    {
        return $this->useHiddenElement;
    }

    /**
     * Set the value if the select is not selected
     *
     * @param string $unselectedValue
     * @return Select
     */
    public function setUnselectedValue($unselectedValue)
    {
        $this->unselectedValue = (string) $unselectedValue;
        return $this;
    }

    /**
     * Get the value when the select is not selected
     *
     * @return string
     */
    public function getUnselectedValue()
    {
        return $this->unselectedValue;
    }

    /**
     * Provide default input rules for this element
     *
     * @return array
     */
    public function getInputSpecification()
    {
        $spec = array(
            'name' => $this->getName(),
            'required' => true,
        );

        if ($this->useHiddenElement() && $this->isMultiple()) {
            $unselectedValue = $this->getUnselectedValue();

            $spec['allow_empty'] = true;
            $spec['continue_if_empty'] = true;
            $spec['filters'] = array(array(
                'name'    => 'Callback',
                'options' => array(
                    'callback' => function ($value) use ($unselectedValue) {
                        if ($value === $unselectedValue) {
                            $value = array();
                        }
                        return $value;
                    }
                )
            ));
        }

        if ($validator = $this->getValidator()) {
            $spec['validators'] = array(
                $validator,
            );
        }

        return $spec;
    }

    /**
     * Get only the values from the options attribute
     *
     * @return array
     */
    protected function getValueOptionsValues()
    {
        $values  = array();
        $options = $this->getValueOptions();
        foreach ($options as $key => $optionSpec) {
            if (is_array($optionSpec) && array_key_exists('options', $optionSpec)) {
                foreach ($optionSpec['options'] as $nestedKey => $nestedOptionSpec) {
                    $values[] = $this->getOptionValue($nestedKey, $nestedOptionSpec);
                }
                continue;
            }

            $values[] = $this->getOptionValue($key, $optionSpec);
        }
        return $values;
    }

    protected function getOptionValue($key, $optionSpec)
    {
        return is_array($optionSpec) ? $optionSpec['value'] : $key;
    }

    /**
     * Element has the multiple attribute
     *
     * @return bool
     */
    public function isMultiple()
    {
        return isset($this->attributes['multiple'])
            && ($this->attributes['multiple'] === true || $this->attributes['multiple'] === 'multiple');
    }
}
