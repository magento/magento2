<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

use Traversable;
use Zend\Filter\FilterChain;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\ValidatorChain;
use Zend\Validator\ValidatorInterface;

class Factory
{
    /**
     * @var FilterChain
     */
    protected $defaultFilterChain;

    /**
     * @var ValidatorChain
     */
    protected $defaultValidatorChain;

    /**
     * @var InputFilterPluginManager
     */
    protected $inputFilterManager;

    /**
     * @param InputFilterPluginManager $inputFilterManager
     */
    public function __construct(InputFilterPluginManager $inputFilterManager = null)
    {
        $this->defaultFilterChain    = new FilterChain();
        $this->defaultValidatorChain = new ValidatorChain();

        if ($inputFilterManager) {
            $this->setInputFilterManager($inputFilterManager);
        }
    }

    /**
     * Set default filter chain to use
     *
     * @param  FilterChain $filterChain
     * @return Factory
     */
    public function setDefaultFilterChain(FilterChain $filterChain)
    {
        $this->defaultFilterChain = $filterChain;
        return $this;
    }

    /**
     * Get default filter chain, if any
     *
     * @return null|FilterChain
     */
    public function getDefaultFilterChain()
    {
        return $this->defaultFilterChain;
    }

    /**
     * Clear the default filter chain (i.e., don't inject one into new inputs)
     *
     * @return void
     */
    public function clearDefaultFilterChain()
    {
        $this->defaultFilterChain = null;
    }

    /**
     * Set default validator chain to use
     *
     * @param  ValidatorChain $validatorChain
     * @return Factory
     */
    public function setDefaultValidatorChain(ValidatorChain $validatorChain)
    {
        $this->defaultValidatorChain = $validatorChain;
        return $this;
    }

    /**
     * Get default validator chain, if any
     *
     * @return null|ValidatorChain
     */
    public function getDefaultValidatorChain()
    {
        return $this->defaultValidatorChain;
    }

    /**
     * Clear the default validator chain (i.e., don't inject one into new inputs)
     *
     * @return void
     */
    public function clearDefaultValidatorChain()
    {
        $this->defaultValidatorChain = null;
    }

    /**
     * @param  InputFilterPluginManager $inputFilterManager
     * @return self
     */
    public function setInputFilterManager(InputFilterPluginManager $inputFilterManager)
    {
        $this->inputFilterManager = $inputFilterManager;
        $serviceLocator = $this->inputFilterManager->getServiceLocator();
        if ($serviceLocator && $serviceLocator instanceof ServiceLocatorInterface) {
            if ($serviceLocator->has('ValidatorManager')) {
                $this->getDefaultValidatorChain()->setPluginManager($serviceLocator->get('ValidatorManager'));
            }
            if ($serviceLocator->has('FilterManager')) {
                $this->getDefaultFilterChain()->setPluginManager($serviceLocator->get('FilterManager'));
            }
        }
        return $this;
    }

    /**
     * @return InputFilterPluginManager
     */
    public function getInputFilterManager()
    {
        if (null === $this->inputFilterManager) {
            $this->inputFilterManager = new InputFilterPluginManager;
        }

        return $this->inputFilterManager;
    }

    /**
     * Factory for input objects
     *
     * @param  array|Traversable|InputProviderInterface $inputSpecification
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     * @return InputInterface|InputFilterInterface
     */
    public function createInput($inputSpecification)
    {
        if ($inputSpecification instanceof InputProviderInterface) {
            $inputSpecification = $inputSpecification->getInputSpecification();
        }

        if (!is_array($inputSpecification) && !$inputSpecification instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($inputSpecification) ? get_class($inputSpecification) : gettype($inputSpecification))
            ));
        }
        if ($inputSpecification instanceof Traversable) {
            $inputSpecification = ArrayUtils::iteratorToArray($inputSpecification);
        }

        $class = 'Zend\InputFilter\Input';

        if (isset($inputSpecification['type'])) {
            $class = $inputSpecification['type'];
        }

        $managerInstance = null;
        if ($this->getInputFilterManager()->has($class)) {
            $managerInstance = $this->getInputFilterManager()->get($class);
        }
        if (! $managerInstance && ! class_exists($class)) {
            throw new Exception\RuntimeException(sprintf(
                'Input factory expects the "type" to be a valid class or a plugin name; received "%s"',
                $class
            ));
        }

        $input = $managerInstance ?: new $class;

        if ($input instanceof InputFilterInterface) {
            return $this->createInputFilter($inputSpecification);
        }

        if (!$input instanceof InputInterface) {
            throw new Exception\RuntimeException(sprintf(
                'Input factory expects the "type" to be a class implementing %s; received "%s"',
                'Zend\InputFilter\InputInterface',
                $class
            ));
        }

        if ($this->defaultFilterChain) {
            $input->setFilterChain(clone $this->defaultFilterChain);
        }
        if ($this->defaultValidatorChain) {
            $input->setValidatorChain(clone $this->defaultValidatorChain);
        }

        foreach ($inputSpecification as $key => $value) {
            switch ($key) {
                case 'name':
                    $input->setName($value);
                    break;
                case 'required':
                    $input->setRequired($value);
                    break;
                case 'allow_empty':
                    $input->setAllowEmpty($value);
                    if (!isset($inputSpecification['required'])) {
                        $input->setRequired(!$value);
                    }
                    break;
                case 'continue_if_empty':
                    if (!$input instanceof Input) {
                        throw new Exception\RuntimeException(sprintf(
                            '%s "continue_if_empty" can only set to inputs of type "%s"',
                            __METHOD__,
                            'Zend\InputFilter\Input'
                        ));
                    }
                    $input->setContinueIfEmpty($inputSpecification['continue_if_empty']);
                    break;
                case 'error_message':
                    $input->setErrorMessage($value);
                    break;
                case 'fallback_value':
                    if (!$input instanceof Input) {
                        throw new Exception\RuntimeException(sprintf(
                            '%s "fallback_value" can only set to inputs of type "%s"',
                            __METHOD__,
                            'Zend\InputFilter\Input'
                        ));
                    }
                    $input->setFallbackValue($value);
                    break;
                case 'break_on_failure':
                    $input->setBreakOnFailure($value);
                    break;
                case 'filters':
                    if ($value instanceof FilterChain) {
                        $input->setFilterChain($value);
                        break;
                    }
                    if (!is_array($value) && !$value instanceof Traversable) {
                        throw new Exception\RuntimeException(sprintf(
                            '%s expects the value associated with "filters" to be an array/Traversable of filters or filter specifications, or a FilterChain; received "%s"',
                            __METHOD__,
                            (is_object($value) ? get_class($value) : gettype($value))
                        ));
                    }
                    $this->populateFilters($input->getFilterChain(), $value);
                    break;
                case 'validators':
                    if ($value instanceof ValidatorChain) {
                        $input->setValidatorChain($value);
                        break;
                    }
                    if (!is_array($value) && !$value instanceof Traversable) {
                        throw new Exception\RuntimeException(sprintf(
                            '%s expects the value associated with "validators" to be an array/Traversable of validators or validator specifications, or a ValidatorChain; received "%s"',
                            __METHOD__,
                            (is_object($value) ? get_class($value) : gettype($value))
                        ));
                    }
                    $this->populateValidators($input->getValidatorChain(), $value);
                    break;
                default:
                    // ignore unknown keys
                    break;
            }
        }

        return $input;
    }

    /**
     * Factory for input filters
     *
     * @param  array|Traversable|InputFilterProviderInterface $inputFilterSpecification
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     * @return InputFilterInterface
     */
    public function createInputFilter($inputFilterSpecification)
    {
        if ($inputFilterSpecification instanceof InputFilterProviderInterface) {
            $inputFilterSpecification = $inputFilterSpecification->getInputFilterSpecification();
        }

        if (!is_array($inputFilterSpecification) && !$inputFilterSpecification instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($inputFilterSpecification) ? get_class($inputFilterSpecification) : gettype($inputFilterSpecification))
            ));
        }
        if ($inputFilterSpecification instanceof Traversable) {
            $inputFilterSpecification = ArrayUtils::iteratorToArray($inputFilterSpecification);
        }

        $type = 'Zend\InputFilter\InputFilter';

        if (isset($inputFilterSpecification['type']) && is_string($inputFilterSpecification['type'])) {
            $type = $inputFilterSpecification['type'];
            unset($inputFilterSpecification['type']);
        }

        $inputFilter = $this->getInputFilterManager()->get($type);

        if ($inputFilter instanceof CollectionInputFilter) {
            $inputFilter->setFactory($this);
            if (isset($inputFilterSpecification['input_filter'])) {
                $inputFilter->setInputFilter($inputFilterSpecification['input_filter']);
            }
            if (isset($inputFilterSpecification['count'])) {
                $inputFilter->setCount($inputFilterSpecification['count']);
            }
            if (isset($inputFilterSpecification['required'])) {
                $inputFilter->setIsRequired($inputFilterSpecification['required']);
            }
            return $inputFilter;
        }

        foreach ($inputFilterSpecification as $key => $value) {
            if (null === $value) {
                continue;
            }

            if (($value instanceof InputInterface)
                || ($value instanceof InputFilterInterface)
            ) {
                $input = $value;
            } else {
                $input = $this->createInput($value);
            }

            $inputFilter->add($input, $key);
        }

        return $inputFilter;
    }

    /**
     * @param  FilterChain       $chain
     * @param  array|Traversable $filters
     * @throws Exception\RuntimeException
     * @return void
     */
    protected function populateFilters(FilterChain $chain, $filters)
    {
        foreach ($filters as $filter) {
            if (is_object($filter) || is_callable($filter)) {
                $chain->attach($filter);
                continue;
            }

            if (is_array($filter)) {
                if (!isset($filter['name'])) {
                    throw new Exception\RuntimeException(
                        'Invalid filter specification provided; does not include "name" key'
                    );
                }
                $name = $filter['name'];
                $priority = isset($filter['priority']) ? $filter['priority'] : FilterChain::DEFAULT_PRIORITY;
                $options = array();
                if (isset($filter['options'])) {
                    $options = $filter['options'];
                }
                $chain->attachByName($name, $options, $priority);
                continue;
            }

            throw new Exception\RuntimeException(
                'Invalid filter specification provided; was neither a filter instance nor an array specification'
            );
        }
    }

    /**
     * @param  ValidatorChain    $chain
     * @param  string[]|ValidatorInterface[] $validators
     * @throws Exception\RuntimeException
     * @return void
     */
    protected function populateValidators(ValidatorChain $chain, $validators)
    {
        foreach ($validators as $validator) {
            if ($validator instanceof ValidatorInterface) {
                $chain->attach($validator);
                continue;
            }

            if (is_array($validator)) {
                if (!isset($validator['name'])) {
                    throw new Exception\RuntimeException(
                        'Invalid validator specification provided; does not include "name" key'
                    );
                }
                $name    = $validator['name'];
                $options = array();
                if (isset($validator['options'])) {
                    $options = $validator['options'];
                }
                $breakChainOnFailure = false;
                if (isset($validator['break_chain_on_failure'])) {
                    $breakChainOnFailure = $validator['break_chain_on_failure'];
                }
                $chain->attachByName($name, $options, $breakChainOnFailure);
                continue;
            }

            throw new Exception\RuntimeException(
                'Invalid validator specification provided; was neither a validator instance nor an array specification'
            );
        }
    }
}
