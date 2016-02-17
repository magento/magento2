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
use Zend\Form\Element\Collection;
use Zend\InputFilter\CollectionInputFilter;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\InputFilter\InputProviderInterface;
use Zend\InputFilter\ReplaceableInputInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Hydrator\HydratorInterface;

class Form extends Fieldset implements FormInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'method' => 'POST',
    );

    /**
     * How to bind values to the attached object
     *
     * @var int
     */
    protected $bindAs = FormInterface::VALUES_NORMALIZED;

    /**
     * Whether or not to bind values to the bound object on successful validation
     *
     * @var int
     */
    protected $bindOnValidate = FormInterface::BIND_ON_VALIDATE;

    /**
     * Base fieldset to use for hydrating (if none specified, directly hydrate elements)
     *
     * @var FieldsetInterface
     */
    protected $baseFieldset;

    /**
     * Data being validated
     *
     * @var null|array|Traversable
     */
    protected $data;

    /**
     * @var null|InputFilterInterface
     */
    protected $filter;

    /**
     * Whether or not to automatically scan for input filter defaults on
     * attached fieldsets and elements
     *
     * @var bool
     */
    protected $useInputFilterDefaults = true;

    /**
     * Has the input filter defaults been added already ?
     *
     * @var bool
     */
    protected $hasAddedInputFilterDefaults = false;

    /**
     * Whether or not validation has occurred
     *
     * @var bool
     */
    protected $hasValidated = false;

    /**
     * Result of last validation operation
     *
     * @var bool
     */
    protected $isValid = false;

    /**
     * Is the form prepared ?
     *
     * @var bool
     */
    protected $isPrepared = false;

    /**
     * Prefer form input filter over input filter defaults
     *
     * @var bool
     */
    protected $preferFormInputFilter = true;

    /**
     * Has preferFormInputFilter been set with setPreferFormInputFilter?
     *
     * @var bool
     */
    protected $hasSetPreferFormInputFilter = false;

    /**
     * Are the form elements/fieldsets wrapped by the form name ?
     *
     * @var bool
     */
    protected $wrapElements = false;

    /**
     * Validation group, if any
     *
     * @var null|array
     */
    protected $validationGroup;

    /**
     * Set options for a form. Accepted options are:
     * - prefer_form_input_filter: is form input filter is preferred?
     *
     * @param  array|Traversable $options
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($options['prefer_form_input_filter'])) {
            $this->setPreferFormInputFilter($options['prefer_form_input_filter']);
        }

        if (isset($options['use_input_filter_defaults'])) {
            $this->setUseInputFilterDefaults($options['use_input_filter_defaults']);
        }

        return $this;
    }

    /**
     * Add an element or fieldset
     *
     * If $elementOrFieldset is an array or Traversable, passes the argument on
     * to the composed factory to create the object before attaching it.
     *
     * $flags could contain metadata such as the alias under which to register
     * the element or fieldset, order in which to prioritize it, etc.
     *
     * @param  array|Traversable|ElementInterface $elementOrFieldset
     * @param  array                              $flags
     * @return self
     */
    public function add($elementOrFieldset, array $flags = array())
    {
        // TODO: find a better solution than duplicating the factory code, the problem being that if $elementOrFieldset is an array,
        // it is passed by value, and we don't get back the concrete ElementInterface
        if (is_array($elementOrFieldset)
            || ($elementOrFieldset instanceof Traversable && !$elementOrFieldset instanceof ElementInterface)
        ) {
            $factory = $this->getFormFactory();
            $elementOrFieldset = $factory->create($elementOrFieldset);
        }

        parent::add($elementOrFieldset, $flags);

        if ($elementOrFieldset instanceof Fieldset && $elementOrFieldset->useAsBaseFieldset()) {
            $this->baseFieldset = $elementOrFieldset;
        }

        return $this;
    }

    /**
     * Ensures state is ready for use
     *
     * Marshalls the input filter, to ensure validation error messages are
     * available, and prepares any elements and/or fieldsets that require
     * preparation.
     *
     * @return self
     */
    public function prepare()
    {
        if ($this->isPrepared) {
            return $this;
        }

        $this->getInputFilter();

        // If the user wants to, elements names can be wrapped by the form's name
        if ($this->wrapElements()) {
            $this->prepareElement($this);
        } else {
            foreach ($this->getIterator() as $elementOrFieldset) {
                if ($elementOrFieldset instanceof FormInterface) {
                    $elementOrFieldset->prepare();
                } elseif ($elementOrFieldset instanceof ElementPrepareAwareInterface) {
                    $elementOrFieldset->prepareElement($this);
                }
            }
        }

        $this->isPrepared = true;
        return $this;
    }

    /**
     * Ensures state is ready for use. Here, we append the name of the fieldsets to every elements in order to avoid
     * name clashes if the same fieldset is used multiple times
     *
     * @param  FormInterface $form
     * @return mixed|void
     */
    public function prepareElement(FormInterface $form)
    {
        $name = $this->getName();

        foreach ($this->iterator as $elementOrFieldset) {
            if ($form->wrapElements()) {
                $elementOrFieldset->setName($name . '[' . $elementOrFieldset->getName() . ']');
            }

            // Recursively prepare elements
            if ($elementOrFieldset instanceof ElementPrepareAwareInterface) {
                $elementOrFieldset->prepareElement($form);
            }
        }
    }

    /**
     * Set data to validate and/or populate elements
     *
     * Typically, also passes data on to the composed input filter.
     *
     * @param  array|\ArrayAccess|Traversable $data
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setData($data)
    {
        if ($data instanceof Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        if (!is_array($data)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable argument; received "%s"',
                __METHOD__,
                (is_object($data) ? get_class($data) : gettype($data))
            ));
        }

        $this->hasValidated = false;
        $this->data         = $data;
        $this->populateValues($data);

        return $this;
    }

    /**
     * Bind an object to the form
     *
     * Ensures the object is populated with validated values.
     *
     * @param  object $object
     * @param  int $flags
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function bind($object, $flags = FormInterface::VALUES_NORMALIZED)
    {
        if (!in_array($flags, array(FormInterface::VALUES_NORMALIZED, FormInterface::VALUES_RAW))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects the $flags argument to be one of "%s" or "%s"; received "%s"',
                __METHOD__,
                'Zend\Form\FormInterface::VALUES_NORMALIZED',
                'Zend\Form\FormInterface::VALUES_RAW',
                $flags
            ));
        }

        if ($this->baseFieldset !== null) {
            $this->baseFieldset->setObject($object);
        }

        $this->bindAs = $flags;
        $this->setObject($object);

        $data = $this->extract();

        $this->populateValues($data, true);

        return $this;
    }

    /**
     * Set the hydrator to use when binding an object to the element
     *
     * @param  HydratorInterface $hydrator
     * @return FieldsetInterface
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        if ($this->baseFieldset !== null) {
            $this->baseFieldset->setHydrator($hydrator);
        }

        return parent::setHydrator($hydrator);
    }

    /**
     * Bind values to the bound object
     *
     * @param array $values
     * @return mixed
     */
    public function bindValues(array $values = array())
    {
        if (!is_object($this->object)) {
            if ($this->baseFieldset === null || $this->baseFieldset->allowValueBinding() == false) {
                return;
            }
        }
        if (!$this->hasValidated() && !empty($values)) {
            $this->setData($values);
            if (!$this->isValid()) {
                return;
            }
        } elseif (!$this->isValid) {
            return;
        }

        $filter = $this->getInputFilter();

        switch ($this->bindAs) {
            case FormInterface::VALUES_RAW:
                $data = $filter->getRawValues();
                break;
            case FormInterface::VALUES_NORMALIZED:
            default:
                $data = $filter->getValues();
                break;
        }

        $data = $this->prepareBindData($data, $this->data);

        // If there is a base fieldset, only hydrate beginning from the base fieldset
        if ($this->baseFieldset !== null) {
            $data = $data[$this->baseFieldset->getName()];
            $this->object = $this->baseFieldset->bindValues($data);
        } else {
            $this->object = parent::bindValues($data);
        }
    }

    /**
     * Parse filtered values and return only posted fields for binding
     *
     * @param array $values
     * @param array $match
     * @return array
     */
    protected function prepareBindData(array $values, array $match)
    {
        $data = array();
        foreach ($values as $name => $value) {
            if (!array_key_exists($name, $match)) {
                continue;
            }

            if (is_array($value) && is_array($match[$name])) {
                $data[$name] = $this->prepareBindData($value, $match[$name]);
            } else {
                $data[$name] = $value;
            }
        }
        return $data;
    }

    /**
     * Set flag indicating whether or not to bind values on successful validation
     *
     * @param  int $bindOnValidateFlag
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setBindOnValidate($bindOnValidateFlag)
    {
        if (!in_array($bindOnValidateFlag, array(self::BIND_ON_VALIDATE, self::BIND_MANUAL))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects the flag to be one of %s::%s or %s::%s',
                __METHOD__,
                get_class($this),
                'BIND_ON_VALIDATE',
                get_class($this),
                'BIND_MANUAL'
            ));
        }
        $this->bindOnValidate = $bindOnValidateFlag;
        return $this;
    }

    /**
     * Will we bind values to the bound object on successful validation?
     *
     * @return bool
     */
    public function bindOnValidate()
    {
        return (static::BIND_ON_VALIDATE === $this->bindOnValidate);
    }

    /**
     * Set the base fieldset to use when hydrating
     *
     * @param  FieldsetInterface $baseFieldset
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setBaseFieldset(FieldsetInterface $baseFieldset)
    {
        $this->baseFieldset = $baseFieldset;
        return $this;
    }

    /**
     * Get the base fieldset to use when hydrating
     *
     * @return FieldsetInterface
     */
    public function getBaseFieldset()
    {
        return $this->baseFieldset;
    }

    /**
     * Check if the form has been validated
     *
     * @return bool
     */
    public function hasValidated()
    {
        return $this->hasValidated;
    }

    /**
     * Validate the form
     *
     * Typically, will proxy to the composed input filter.
     *
     * @return bool
     * @throws Exception\DomainException
     */
    public function isValid()
    {
        if ($this->hasValidated) {
            return $this->isValid;
        }

        $this->isValid = false;

        if (!is_array($this->data) && !is_object($this->object)) {
            throw new Exception\DomainException(sprintf(
                '%s is unable to validate as there is no data currently set',
                __METHOD__
            ));
        }

        if (!is_array($this->data)) {
            $data = $this->extract();
            $this->populateValues($data, true);
            if (!is_array($data)) {
                throw new Exception\DomainException(sprintf(
                    '%s is unable to validate as there is no data currently set',
                    __METHOD__
                ));
            }
            $this->data = $data;
        }

        $filter = $this->getInputFilter();
        if (!$filter instanceof InputFilterInterface) {
            throw new Exception\DomainException(sprintf(
                '%s is unable to validate as there is no input filter present',
                __METHOD__
            ));
        }

        $filter->setData($this->data);
        $filter->setValidationGroup(InputFilterInterface::VALIDATE_ALL);

        $validationGroup = $this->getValidationGroup();
        if ($validationGroup !== null) {
            $this->prepareValidationGroup($this, $this->data, $validationGroup);
            $filter->setValidationGroup($validationGroup);
        }

        $this->isValid = $result = $filter->isValid();
        $this->hasValidated = true;

        if ($result && $this->bindOnValidate()) {
            $this->bindValues();
        }

        if (!$result) {
            $this->setMessages($filter->getMessages());
        }

        return $result;
    }

    /**
     * Retrieve the validated data
     *
     * By default, retrieves normalized values; pass one of the
     * FormInterface::VALUES_* constants to shape the behavior.
     *
     * @param  int $flag
     * @return array|object
     * @throws Exception\DomainException
     */
    public function getData($flag = FormInterface::VALUES_NORMALIZED)
    {
        if (!$this->hasValidated) {
            throw new Exception\DomainException(sprintf(
                '%s cannot return data as validation has not yet occurred',
                __METHOD__
            ));
        }

        if (($flag !== FormInterface::VALUES_AS_ARRAY) && is_object($this->object)) {
            return $this->object;
        }

        $filter = $this->getInputFilter();

        if ($flag === FormInterface::VALUES_RAW) {
            return $filter->getRawValues();
        }

        return $filter->getValues();
    }

    /**
     * Set the validation group (set of values to validate)
     *
     * Typically, proxies to the composed input filter
     *
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setValidationGroup()
    {
        $argc = func_num_args();
        if (0 === $argc) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects at least one argument; none provided',
                __METHOD__
            ));
        }

        $argv = func_get_args();
        $this->hasValidated = false;

        if ($argc > 1) {
            $this->validationGroup = $argv;
            return $this;
        }

        $arg = array_shift($argv);
        if ($arg === FormInterface::VALIDATE_ALL) {
            $this->validationGroup = null;
            return $this;
        }

        if (!is_array($arg)) {
            $arg = (array) $arg;
        }

        $this->validationGroup = $arg;
        return $this;
    }

    /**
     * Retrieve the current validation group, if any
     *
     * @return null|array
     */
    public function getValidationGroup()
    {
        return $this->validationGroup;
    }

    /**
     * Prepare the validation group in case Collection elements were used (this function also handle the case where elements
     * could have been dynamically added or removed from a collection using JavaScript)
     *
     * @param FieldsetInterface $formOrFieldset
     * @param array             $data
     * @param array             $validationGroup
     */
    protected function prepareValidationGroup(FieldsetInterface $formOrFieldset, array $data, array &$validationGroup)
    {
        foreach ($validationGroup as $key => &$value) {
            if (!$formOrFieldset->has($key)) {
                continue;
            }

            $fieldset = $formOrFieldset->iterator->get($key);

            if ($fieldset instanceof Collection) {
                if (!isset($data[$key]) && $fieldset->getCount() == 0) {
                    unset($validationGroup[$key]);
                    continue;
                }

                $values = array();

                if (isset($data[$key])) {
                    foreach (array_keys($data[$key]) as $cKey) {
                        $values[$cKey] = $value;
                    }
                }

                $value = $values;
            }

            if (!isset($data[$key])) {
                $data[$key] = array();
            }
            $this->prepareValidationGroup($fieldset, $data[$key], $validationGroup[$key]);
        }
    }

    /**
     * Set the input filter used by this form
     *
     * @param  InputFilterInterface $inputFilter
     * @return self
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->hasValidated                = false;
        $this->hasAddedInputFilterDefaults = false;
        $this->filter                      = $inputFilter;

        if (false === $this->hasSetPreferFormInputFilter) {
            $this->preferFormInputFilter = false;
        }

        return $this;
    }

    /**
     * Retrieve input filter used by this form
     *
     * @return null|InputFilterInterface
     */
    public function getInputFilter()
    {
        if ($this->object instanceof InputFilterAwareInterface) {
            if (null == $this->baseFieldset) {
                $this->filter = $this->object->getInputFilter();
            } else {
                $name = $this->baseFieldset->getName();
                if (!$this->filter instanceof InputFilterInterface || !$this->filter->has($name)) {
                    $filter = new InputFilter();
                    $filter->add($this->object->getInputFilter(), $name);
                    $this->filter = $filter;
                }
            }
        }

        if (!isset($this->filter)) {
            $this->filter = new InputFilter();
        }

        if (!$this->hasAddedInputFilterDefaults
            && $this->filter instanceof InputFilterInterface
            && $this->useInputFilterDefaults()
        ) {
            $this->attachInputFilterDefaults($this->filter, $this);
            $this->hasAddedInputFilterDefaults = true;
        }

        return $this->filter;
    }

    /**
     * Set flag indicating whether or not to scan elements and fieldsets for defaults
     *
     * @param  bool $useInputFilterDefaults
     * @return self
     */
    public function setUseInputFilterDefaults($useInputFilterDefaults)
    {
        $this->useInputFilterDefaults = (bool) $useInputFilterDefaults;
        return $this;
    }

    /**
     * Should we use input filter defaults from elements and fieldsets?
     *
     * @return bool
     */
    public function useInputFilterDefaults()
    {
        return $this->useInputFilterDefaults;
    }

    /**
     * Set flag indicating whether or not to prefer the form input filter over element and fieldset defaults
     *
     * @param  bool $preferFormInputFilter
     * @return self
     */
    public function setPreferFormInputFilter($preferFormInputFilter)
    {
        $this->preferFormInputFilter = (bool) $preferFormInputFilter;
        $this->hasSetPreferFormInputFilter = true;
        return $this;
    }

    /**
     * Should we use form input filter over element input filter defaults from elements and fieldsets?
     *
     * @return bool
     */
    public function getPreferFormInputFilter()
    {
        return $this->preferFormInputFilter;
    }

    /**
     * Attach defaults provided by the elements to the input filter
     *
     * @param  InputFilterInterface $inputFilter
     * @param  FieldsetInterface $fieldset Fieldset to traverse when looking for default inputs
     * @return void
     */
    public function attachInputFilterDefaults(InputFilterInterface $inputFilter, FieldsetInterface $fieldset)
    {
        $formFactory  = $this->getFormFactory();
        $inputFactory = $formFactory->getInputFilterFactory();

        if ($fieldset instanceof Collection && $fieldset->getTargetElement() instanceof FieldsetInterface) {
            $elements = $fieldset->getTargetElement()->getElements();
        } else {
            $elements = $fieldset->getElements();
        }

        if (!$fieldset instanceof Collection || !$fieldset->getTargetElement() instanceof FieldsetInterface || $inputFilter instanceof CollectionInputFilter) {
            foreach ($elements as $name => $element) {
                if ($this->preferFormInputFilter && $inputFilter->has($name)) {
                    continue;
                }

                if (!$element instanceof InputProviderInterface) {
                    if ($inputFilter->has($name)) {
                        continue;
                    }
                    // Create a new empty default input for this element
                    $spec  = array('name' => $name, 'required' => false);
                    $input = $inputFactory->createInput($spec);
                } else {
                    // Create an input based on the specification returned from the element
                    $spec  = $element->getInputSpecification();
                    $input = $inputFactory->createInput($spec);

                    if ($inputFilter->has($name) && $inputFilter instanceof ReplaceableInputInterface) {
                        $input->merge($inputFilter->get($name));
                        $inputFilter->replace($input, $name);
                        continue;
                    }
                }

                // Add element input filter to CollectionInputFilter
                if ($inputFilter instanceof CollectionInputFilter && !$inputFilter->getInputFilter()->has($name)) {
                    $inputFilter->getInputFilter()->add($input, $name);
                } else {
                    $inputFilter->add($input, $name);
                }
            }

            if ($fieldset === $this && $fieldset instanceof InputFilterProviderInterface) {
                foreach ($fieldset->getInputFilterSpecification() as $name => $spec) {
                    $input = $inputFactory->createInput($spec);
                    $inputFilter->add($input, $name);
                }
            }
        }

        foreach ($fieldset->getFieldsets() as $name => $childFieldset) {
            if (!$childFieldset instanceof InputFilterProviderInterface) {
                if (!$inputFilter->has($name)) {
                    // Add a new empty input filter if it does not exist (or the fieldset's object input filter),
                    // so that elements of nested fieldsets can be recursively added
                    if ($childFieldset->getObject() instanceof InputFilterAwareInterface) {
                        $inputFilter->add($childFieldset->getObject()->getInputFilter(), $name);
                    } else {
                        // Add input filter for collections via getInputFilterSpecification()
                        if ($childFieldset instanceof Collection
                            && $childFieldset->getTargetElement() instanceof InputFilterProviderInterface
                            && $childFieldset->getTargetElement()->getInputFilterSpecification()
                        ) {
                            $collectionContainerFilter = new CollectionInputFilter();

                            $spec = $childFieldset->getTargetElement()->getInputFilterSpecification();
                            $filter = $inputFactory->createInputFilter($spec);

                            $collectionContainerFilter->setInputFilter($filter);

                            $inputFilter->add($collectionContainerFilter, $name);

                            // We need to copy the inputs to the collection input filter
                            if ($inputFilter instanceof CollectionInputFilter) {
                                $inputFilter = $this->addInputsToCollectionInputFilter($inputFilter);
                            }

                            // Add child elements from target element
                            $childFieldset = $childFieldset->getTargetElement();
                        } else {
                            $inputFilter->add(new InputFilter(), $name);
                        }
                    }
                }

                $fieldsetFilter = $inputFilter->get($name);

                if (!$fieldsetFilter instanceof InputFilterInterface) {
                    // Input attached for fieldset, not input filter; nothing more to do.
                    continue;
                }

                // Traverse the elements of the fieldset, and attach any
                // defaults to the fieldset's input filter
                $this->attachInputFilterDefaults($fieldsetFilter, $childFieldset);
                continue;
            }

            if ($inputFilter->has($name)) {
                // if we already have an input/filter by this name, use it
                continue;
            }

            // Create an input filter based on the specification returned from the fieldset
            $spec   = $childFieldset->getInputFilterSpecification();
            $filter = $inputFactory->createInputFilter($spec);
            $inputFilter->add($filter, $name);

            // Recursively attach sub filters
            $this->attachInputFilterDefaults($filter, $childFieldset);

            // We need to copy the inputs to the collection input filter to ensure that all sub filters are added
            if ($inputFilter instanceof CollectionInputFilter) {
                $inputFilter = $this->addInputsToCollectionInputFilter($inputFilter);
            }
        }
    }

    /**
     * Add inputs to CollectionInputFilter
     *
     * @param  CollectionInputFilter $inputFilter
     * @return CollectionInputFilter
     */
    private function addInputsToCollectionInputFilter(CollectionInputFilter $inputFilter)
    {
        foreach ($inputFilter->getInputs() as $name => $input) {
            if (!$inputFilter->getInputFilter()->has($name)) {
                $inputFilter->getInputFilter()->add($input, $name);
            }
        }

        return $inputFilter;
    }

    /**
     * Are the form elements/fieldsets names wrapped by the form name ?
     *
     * @param  bool $wrapElements
     * @return self
     */
    public function setWrapElements($wrapElements)
    {
        $this->wrapElements = (bool) $wrapElements;
        return $this;
    }

    /**
     * If true, form elements/fieldsets name's are wrapped around the form name itself
     *
     * @return bool
     */
    public function wrapElements()
    {
        return $this->wrapElements;
    }

    /**
     * {@inheritDoc}
     *
     * @param bool $onlyBase
     */
    public function populateValues($data, $onlyBase = false)
    {
        if ($onlyBase && $this->baseFieldset !== null) {
            $name = $this->baseFieldset->getName();
            if (array_key_exists($name, $data)) {
                $this->baseFieldset->populateValues($data[$name]);
            }
        } else {
            parent::populateValues($data);
        }
    }

    /**
     * Recursively extract values for elements and sub-fieldsets
     *
     * @return array
     */
    protected function extract()
    {
        if (null !== $this->baseFieldset) {
            $name = $this->baseFieldset->getName();
            $values[$name] = $this->baseFieldset->extract();
        } else {
            $values = parent::extract();
        }

        return $values;
    }
}
