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
use Zend\Code\Reflection\ClassReflection;
use Zend\Stdlib\Hydrator;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\PriorityList;

class Fieldset extends Element implements FieldsetInterface
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $elements  = array();

    /**
     * @var array
     */
    protected $fieldsets = array();

    /**
     * @var array
     */
    protected $messages  = array();

    /**
     * @var PriorityList
     */
    protected $iterator;

    /**
     * Hydrator to use with bound object
     *
     * @var Hydrator\HydratorInterface
     */
    protected $hydrator;

    /**
     * The object bound to this fieldset, if any
     *
     * @var null|object
     */
    protected $object;

    /**
     * Should this fieldset be used as a base fieldset in the parent form ?
     *
     * @var bool
     */
    protected $useAsBaseFieldset = false;

    /**
     * The class or interface of objects that can be bound to this fieldset.
     *
     * @var string
     */
    protected $allowedObjectBindingClass;

    /**
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = null, $options = array())
    {
        $this->iterator = new PriorityList();
        $this->iterator->isLIFO(false);
        parent::__construct($name, $options);
    }

    /**
     * Set options for a fieldset. Accepted options are:
     * - use_as_base_fieldset: is this fieldset use as the base fieldset?
     *
     * @param  array|Traversable $options
     * @return Element|ElementInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($options['use_as_base_fieldset'])) {
            $this->setUseAsBaseFieldset($options['use_as_base_fieldset']);
        }

        if (isset($options['allowed_object_binding_class'])) {
            $this->setAllowedObjectBindingClass($options['allowed_object_binding_class']);
        }

        return $this;
    }

    /**
     * Compose a form factory to use when calling add() with a non-element/fieldset
     *
     * @param  Factory $factory
     * @return Form
     */
    public function setFormFactory(Factory $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * Retrieve composed form factory
     *
     * Lazy-loads one if none present.
     *
     * @return Factory
     */
    public function getFormFactory()
    {
        if (null === $this->factory) {
            $this->setFormFactory(new Factory());
        }

        return $this->factory;
    }

    /**
     * Add an element or fieldset
     *
     * $flags could contain metadata such as the alias under which to register
     * the element or fieldset, order in which to prioritize it, etc.
     *
     * @todo   Should we detect if the element/fieldset name conflicts?
     * @param  array|Traversable|ElementInterface $elementOrFieldset
     * @param  array                              $flags
     * @return Fieldset|FieldsetInterface
     * @throws Exception\InvalidArgumentException
     */
    public function add($elementOrFieldset, array $flags = array())
    {
        if (is_array($elementOrFieldset)
            || ($elementOrFieldset instanceof Traversable && !$elementOrFieldset instanceof ElementInterface)
        ) {
            $factory = $this->getFormFactory();
            $elementOrFieldset = $factory->create($elementOrFieldset);
        }

        if (!$elementOrFieldset instanceof ElementInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that $elementOrFieldset be an object implementing %s; received "%s"',
                __METHOD__,
                __NAMESPACE__ . '\ElementInterface',
                (is_object($elementOrFieldset) ? get_class($elementOrFieldset) : gettype($elementOrFieldset))
            ));
        }

        $name = $elementOrFieldset->getName();
        if ((null === $name || '' === $name)
            && (!array_key_exists('name', $flags) || $flags['name'] === '')
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: element or fieldset provided is not named, and no name provided in flags',
                __METHOD__
            ));
        }

        if (array_key_exists('name', $flags) && $flags['name'] !== '') {
            $name = $flags['name'];

            // Rename the element or fieldset to the specified alias
            $elementOrFieldset->setName($name);
        }
        $order = 0;
        if (array_key_exists('priority', $flags)) {
            $order = $flags['priority'];
        }

        $this->iterator->insert($name, $elementOrFieldset, $order);

        if ($elementOrFieldset instanceof FieldsetInterface) {
            $this->fieldsets[$name] = $elementOrFieldset;
            return $this;
        }

        $this->elements[$name] = $elementOrFieldset;
        return $this;
    }

    /**
     * Does the fieldset have an element/fieldset by the given name?
     *
     * @param  string $elementOrFieldset
     * @return bool
     */
    public function has($elementOrFieldset)
    {
        return $this->iterator->get($elementOrFieldset) !== null;
    }

    /**
     * Retrieve a named element or fieldset
     *
     * @param  string $elementOrFieldset
     * @return ElementInterface
     */
    public function get($elementOrFieldset)
    {
        if (!$this->has($elementOrFieldset)) {
            throw new Exception\InvalidElementException(sprintf(
                "No element by the name of [%s] found in form",
                $elementOrFieldset
            ));
        }
        return $this->iterator->get($elementOrFieldset);
    }

    /**
     * Remove a named element or fieldset
     *
     * @param  string $elementOrFieldset
     * @return FieldsetInterface
     */
    public function remove($elementOrFieldset)
    {
        if (!$this->has($elementOrFieldset)) {
            return $this;
        }

        $this->iterator->remove($elementOrFieldset);

        if (isset($this->fieldsets[$elementOrFieldset])) {
            unset($this->fieldsets[$elementOrFieldset]);
            return $this;
        }

        unset($this->elements[$elementOrFieldset]);
        return $this;
    }

    /**
     * Set/change the priority of an element or fieldset
     *
     * @param string $elementOrFieldset
     * @param int $priority
     * @return FieldsetInterface
     */
    public function setPriority($elementOrFieldset, $priority)
    {
        $this->iterator->setPriority($elementOrFieldset, $priority);
        return $this;
    }

    /**
     * Retrieve all attached elements
     *
     * Storage is an implementation detail of the concrete class.
     *
     * @return array|Traversable
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Retrieve all attached fieldsets
     *
     * Storage is an implementation detail of the concrete class.
     *
     * @return array|Traversable
     */
    public function getFieldsets()
    {
        return $this->fieldsets;
    }

    /**
     * Set a hash of element names/messages to use when validation fails
     *
     * @param  array|Traversable $messages
     * @return Element|ElementInterface|FieldsetInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setMessages($messages)
    {
        if (!is_array($messages) && !$messages instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable object of messages; received "%s"',
                __METHOD__,
                (is_object($messages) ? get_class($messages) : gettype($messages))
            ));
        }

        foreach ($messages as $key => $messageSet) {
            if (!$this->has($key)) {
                continue;
            }
            $element = $this->get($key);
            $element->setMessages($messageSet);
        }

        return $this;
    }

    /**
     * Get validation error messages, if any
     *
     * Returns a hash of element names/messages for all elements failing
     * validation, or, if $elementName is provided, messages for that element
     * only.
     *
     * @param  null|string $elementName
     * @return array|Traversable
     * @throws Exception\InvalidArgumentException
     */
    public function getMessages($elementName = null)
    {
        if (null === $elementName) {
            $messages = array();
            foreach ($this->iterator as $name => $element) {
                $messageSet = $element->getMessages();
                if (!is_array($messageSet)
                    && !$messageSet instanceof Traversable
                    || empty($messageSet)) {
                    continue;
                }
                $messages[$name] = $messageSet;
            }
            return $messages;
        }

        if (!$this->has($elementName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid element name "%s" provided to %s',
                $elementName,
                __METHOD__
            ));
        }

        $element = $this->get($elementName);
        return $element->getMessages();
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
            $elementOrFieldset->setName($name . '[' . $elementOrFieldset->getName() . ']');

            // Recursively prepare elements
            if ($elementOrFieldset instanceof ElementPrepareAwareInterface) {
                $elementOrFieldset->prepareElement($form);
            }
        }
    }

    /**
     * Recursively populate values of attached elements and fieldsets
     *
     * @param  array|Traversable $data
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function populateValues($data)
    {
        if (!is_array($data) && !$data instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable set of data; received "%s"',
                __METHOD__,
                (is_object($data) ? get_class($data) : gettype($data))
            ));
        }

        foreach ($this->iterator as $name => $elementOrFieldset) {
            $valueExists = array_key_exists($name, $data);

            if ($elementOrFieldset instanceof FieldsetInterface) {
                if ($valueExists && (is_array($data[$name]) || $data[$name] instanceof Traversable)) {
                    $elementOrFieldset->populateValues($data[$name]);
                    continue;
                }

                if ($elementOrFieldset instanceof Element\Collection) {
                    if ($valueExists && null !== $data[$name]) {
                        $elementOrFieldset->populateValues($data[$name]);
                        continue;
                    }

                    /* This ensures that collections with allow_remove don't re-create child
                     * elements if they all were removed */
                    $elementOrFieldset->populateValues(array());
                    continue;
                }
            }

            if ($valueExists) {
                $elementOrFieldset->setValue($data[$name]);
            }
        }
    }

    /**
     * Countable: return count of attached elements/fieldsets
     *
     * @return int
     */
    public function count()
    {
        return $this->iterator->count();
    }

    /**
     * IteratorAggregate: return internal iterator
     *
     * @return PriorityList
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * Set the object used by the hydrator
     *
     * @param  object $object
     * @return Fieldset|FieldsetInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setObject($object)
    {
        if (!is_object($object)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an object argument; received "%s"',
                __METHOD__,
                $object
            ));
        }

        $this->object = $object;
        return $this;
    }

    /**
     * Get the object used by the hydrator
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set the class or interface of objects that can be bound to this fieldset.
     *
     * @param string $allowObjectBindingClass
     */
    public function setAllowedObjectBindingClass($allowObjectBindingClass)
    {
        $this->allowedObjectBindingClass = $allowObjectBindingClass;
    }

    /**
     * Get The class or interface of objects that can be bound to this fieldset.
     *
     * @return string
     */
    public function allowedObjectBindingClass()
    {
        return $this->allowedObjectBindingClass;
    }

    /**
     * Checks if the object can be set in this fieldset
     *
     * @param object $object
     * @return bool
     */
    public function allowObjectBinding($object)
    {
        $validBindingClass = false;
        if (is_object($object) && $this->allowedObjectBindingClass()) {
            $objectClass = ltrim($this->allowedObjectBindingClass(), '\\');
            $reflection = new ClassReflection($object);
            $validBindingClass = (
                $reflection->getName() == $objectClass
                || $reflection->isSubclassOf($this->allowedObjectBindingClass())
            );
        }

        return ($validBindingClass || $this->object && $object instanceof $this->object);
    }

    /**
     * Set the hydrator to use when binding an object to the element
     *
     * @param  HydratorInterface $hydrator
     * @return FieldsetInterface
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * Get the hydrator used when binding an object to the fieldset
     *
     * If no hydrator is present and object implements HydratorAwareInterface,
     * hydrator will be retrieved from the object.
     *
     * Will lazy-load Hydrator\ArraySerializable if none is present.
     *
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        if (!$this->hydrator instanceof HydratorInterface) {
            if ($this->object instanceof HydratorAwareInterface) {
                $this->setHydrator($this->object->getHydrator());
            } else {
                $this->setHydrator(new Hydrator\ArraySerializable());
            }
        }
        return $this->hydrator;
    }

    /**
     * Checks if this fieldset can bind data
     *
     * @return bool
     */
    public function allowValueBinding()
    {
        return is_object($this->object);
    }

    /**
     * Bind values to the bound object
     *
     * @param array $values
     * @return mixed|void
     */
    public function bindValues(array $values = array())
    {
        $objectData = $this->extract();
        $hydrator = $this->getHydrator();
        $hydratableData = array();

        foreach ($values as $name => $value) {
            if (!$this->has($name)) {
                continue;
            }

            $element = $this->iterator->get($name);

            if ($element instanceof FieldsetInterface && $element->allowValueBinding()) {
                $value = $element->bindValues($value);
            }

            // skip post values for disabled elements, get old value from object
            if (!$element->getAttribute('disabled')) {
                $hydratableData[$name] = $value;
            } elseif (array_key_exists($name, $objectData)) {
                $hydratableData[$name] = $objectData[$name];
            }
        }

        if (!empty($hydratableData)) {
            $this->object = $hydrator->hydrate($hydratableData, $this->object);
        }

        return $this->object;
    }

    /**
     * Set if this fieldset is used as a base fieldset
     *
     * @param  bool $useAsBaseFieldset
     * @return Fieldset
     */
    public function setUseAsBaseFieldset($useAsBaseFieldset)
    {
        $this->useAsBaseFieldset = (bool) $useAsBaseFieldset;
        return $this;
    }

    /**
     * Is this fieldset use as a base fieldset for a form ?
     *
     * @return bool
     */
    public function useAsBaseFieldset()
    {
        return $this->useAsBaseFieldset;
    }

    /**
     * Extract values from the bound object
     *
     * @return array
     */
    protected function extract()
    {
        if (!is_object($this->object)) {
            return array();
        }

        $hydrator = $this->getHydrator();
        if (!$hydrator instanceof Hydrator\HydratorInterface) {
            return array();
        }

        $values = $hydrator->extract($this->object);

        if (!is_array($values)) {
            // Do nothing if the hydrator returned a non-array
            return array();
        }

        // Recursively extract and populate values for nested fieldsets
        foreach ($this->fieldsets as $fieldset) {
            $name = $fieldset->getName();

            if (isset($values[$name])) {
                $object = $values[$name];

                if ($fieldset->allowObjectBinding($object)) {
                    $fieldset->setObject($object);
                    $values[$name] = $fieldset->extract();
                }
            }
        }

        return $values;
    }

    /**
     * Make a deep clone of a fieldset
     *
     * @return void
     */
    public function __clone()
    {
        $items = $this->iterator->toArray(PriorityList::EXTR_BOTH);

        $this->elements  = array();
        $this->fieldsets = array();
        $this->iterator  = new PriorityList();
        $this->iterator->isLIFO(false);

        foreach ($items as $name => $item) {
            $elementOrFieldset = clone $item['data'];

            $this->iterator->insert($name, $elementOrFieldset, $item['priority']);

            if ($elementOrFieldset instanceof FieldsetInterface) {
                $this->fieldsets[$name] = $elementOrFieldset;
            } elseif ($elementOrFieldset instanceof ElementInterface) {
                $this->elements[$name] = $elementOrFieldset;
            }
        }
        $this->iterator->rewind();
        // Also make a deep copy of the object in case it's used within a collection
        if (is_object($this->object)) {
            $this->object = clone $this->object;
        }
    }
}
