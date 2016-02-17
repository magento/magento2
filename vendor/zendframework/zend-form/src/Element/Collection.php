<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Element;

use Traversable;
use Zend\Form\Element;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\Fieldset;
use Zend\Form\FieldsetInterface;
use Zend\Form\FormInterface;
use Zend\Stdlib\ArrayUtils;

class Collection extends Fieldset
{
    /**
     * Default template placeholder
     */
    const DEFAULT_TEMPLATE_PLACEHOLDER = '__index__';

    /**
     * Element used in the collection
     *
     * @var ElementInterface
     */
    protected $targetElement;

    /**
     * Initial count of target element
     *
     * @var int
     */
    protected $count = 1;

    /**
     * Are new elements allowed to be added dynamically ?
     *
     * @var bool
     */
    protected $allowAdd = true;

    /**
     * Are existing elements allowed to be removed dynamically ?
     *
     * @var bool
     */
    protected $allowRemove = true;

    /**
     * Is the template generated ?
     *
     * @var bool
     */
    protected $shouldCreateTemplate = false;

    /**
     * Placeholder used in template content for making your life easier with JavaScript
     *
     * @var string
     */
    protected $templatePlaceholder = self::DEFAULT_TEMPLATE_PLACEHOLDER;

    /**
     * Whether or not to create new objects during modify
     *
     * @var bool
     */
    protected $createNewObjects = false;

    /**
     * Element used as a template
     *
     * @var ElementInterface|FieldsetInterface
     */
    protected $templateElement;

    /**
     * The index of the last child element or fieldset
     *
     * @var int
     */
    protected $lastChildIndex = -1;

    /**
     * Should child elements must be created on self::prepareElement()?
     *
     * @var bool
     */
    protected $shouldCreateChildrenOnPrepareElement = true;

    /**
     * Accepted options for Collection:
     * - target_element: an array or element used in the collection
     * - count: number of times the element is added initially
     * - allow_add: if set to true, elements can be added to the form dynamically (using JavaScript)
     * - allow_remove: if set to true, elements can be removed to the form
     * - should_create_template: if set to true, a template is generated (inside a <span>)
     * - template_placeholder: placeholder used in the data template
     *
     * @param array|Traversable $options
     * @return Collection
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($options['target_element'])) {
            $this->setTargetElement($options['target_element']);
        }

        if (isset($options['count'])) {
            $this->setCount($options['count']);
        }

        if (isset($options['allow_add'])) {
            $this->setAllowAdd($options['allow_add']);
        }

        if (isset($options['allow_remove'])) {
            $this->setAllowRemove($options['allow_remove']);
        }

        if (isset($options['should_create_template'])) {
            $this->setShouldCreateTemplate($options['should_create_template']);
        }

        if (isset($options['template_placeholder'])) {
            $this->setTemplatePlaceholder($options['template_placeholder']);
        }

        if (isset($options['create_new_objects'])) {
            $this->setCreateNewObjects($options['create_new_objects']);
        }

        return $this;
    }

    /**
     * Checks if the object can be set in this fieldset
     *
     * @param object $object
     * @return bool
     */
    public function allowObjectBinding($object)
    {
        return true;
    }

    /**
     * Set the object used by the hydrator
     * In this case the "object" is a collection of objects
     *
     * @param  array|Traversable $object
     * @return Fieldset|FieldsetInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setObject($object)
    {
        if (!is_array($object) && !$object instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable object argument; received "%s"',
                __METHOD__,
                (is_object($object) ? get_class($object) : gettype($object))
            ));
        }

        $this->object = $object;
        $this->count  = count($object) > $this->count ? count($object) : $this->count;

        return $this;
    }

    /**
     * Populate values
     *
     * @param array|Traversable $data
     * @throws \Zend\Form\Exception\InvalidArgumentException
     * @throws \Zend\Form\Exception\DomainException
     * @return void
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

        // Can't do anything with empty data
        if (empty($data)) {
            return;
        }

        if (!$this->allowRemove && count($data) < $this->count) {
            throw new Exception\DomainException(sprintf(
                'There are fewer elements than specified in the collection (%s). Either set the allow_remove option '
                . 'to true, or re-submit the form.',
                get_class($this)
            ));
        }

        // Check to see if elements have been replaced or removed
        $toRemove = array();
        foreach ($this as $name => $elementOrFieldset) {
            if (isset($data[$name])) {
                continue;
            }

            if (!$this->allowRemove) {
                throw new Exception\DomainException(sprintf(
                    'Elements have been removed from the collection (%s) but the allow_remove option is not true.',
                    get_class($this)
                ));
            }

            $toRemove[] = $name;
        }

        foreach ($toRemove as $name) {
            $this->remove($name);
        }

        foreach ($data as $key => $value) {
            if ($this->has($key)) {
                $elementOrFieldset = $this->get($key);
            } else {
                $elementOrFieldset = $this->addNewTargetElementInstance($key);

                if ($key > $this->lastChildIndex) {
                    $this->lastChildIndex = $key;
                }
            }

            if ($elementOrFieldset instanceof FieldsetInterface) {
                $elementOrFieldset->populateValues($value);
            } else {
                $elementOrFieldset->setAttribute('value', $value);
            }
        }

        if (!$this->createNewObjects()) {
            $this->replaceTemplateObjects();
        }
    }

    /**
     * Checks if this fieldset can bind data
     *
     * @return bool
     */
    public function allowValueBinding()
    {
        return true;
    }

    /**
     * Bind values to the object
     *
     * @param array $values
     * @return array|mixed|void
     */
    public function bindValues(array $values = array())
    {
        $collection = array();
        foreach ($values as $name => $value) {
            $element = $this->get($name);

            if ($element instanceof FieldsetInterface) {
                $collection[] = $element->bindValues($value);
            } else {
                $collection[] = $value;
            }
        }

        return $collection;
    }

    /**
     * Set the initial count of target element
     *
     * @param $count
     * @return Collection
     */
    public function setCount($count)
    {
        $this->count = $count > 0 ? $count : 0;
        return $this;
    }

    /**
     * Get the initial count of target element
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set the target element
     *
     * @param ElementInterface|array|Traversable $elementOrFieldset
     * @return Collection
     * @throws \Zend\Form\Exception\InvalidArgumentException
     */
    public function setTargetElement($elementOrFieldset)
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

        $this->targetElement = $elementOrFieldset;

        return $this;
    }

    /**
     * Get target element
     *
     * @return ElementInterface|null
     */
    public function getTargetElement()
    {
        return $this->targetElement;
    }

    /**
     * Get allow add
     *
     * @param bool $allowAdd
     * @return Collection
     */
    public function setAllowAdd($allowAdd)
    {
        $this->allowAdd = (bool) $allowAdd;
        return $this;
    }

    /**
     * Get allow add
     *
     * @return bool
     */
    public function allowAdd()
    {
        return $this->allowAdd;
    }

    /**
     * @param bool $allowRemove
     * @return Collection
     */
    public function setAllowRemove($allowRemove)
    {
        $this->allowRemove = (bool) $allowRemove;
        return $this;
    }

    /**
     * @return bool
     */
    public function allowRemove()
    {
        return $this->allowRemove;
    }

    /**
     * If set to true, a template prototype is automatically added to the form
     * to ease the creation of dynamic elements through JavaScript
     *
     * @param bool $shouldCreateTemplate
     * @return Collection
     */
    public function setShouldCreateTemplate($shouldCreateTemplate)
    {
        $this->shouldCreateTemplate = (bool) $shouldCreateTemplate;

        return $this;
    }

    /**
     * Get if the collection should create a template
     *
     * @return bool
     */
    public function shouldCreateTemplate()
    {
        return $this->shouldCreateTemplate;
    }

    /**
     * Set the placeholder used in the template generated to help create new elements in JavaScript
     *
     * @param string $templatePlaceholder
     * @return Collection
     */
    public function setTemplatePlaceholder($templatePlaceholder)
    {
        if (is_string($templatePlaceholder)) {
            $this->templatePlaceholder = $templatePlaceholder;
        }

        return $this;
    }

    /**
     * Get the template placeholder
     *
     * @return string
     */
    public function getTemplatePlaceholder()
    {
        return $this->templatePlaceholder;
    }

    /**
     * @param bool $createNewObjects
     * @return Collection
     */
    public function setCreateNewObjects($createNewObjects)
    {
        $this->createNewObjects = (bool) $createNewObjects;
        return $this;
    }

    /**
     * @return bool
     */
    public function createNewObjects()
    {
        return $this->createNewObjects;
    }

    /**
     * Get a template element used for rendering purposes only
     *
     * @return null|ElementInterface|FieldsetInterface
     */
    public function getTemplateElement()
    {
        if ($this->templateElement === null) {
            $this->templateElement = $this->createTemplateElement();
        }

        return $this->templateElement;
    }

    /**
     * Prepare the collection by adding a dummy template element if the user want one
     *
     * @param  FormInterface $form
     * @return mixed|void
     */
    public function prepareElement(FormInterface $form)
    {
        if (true === $this->shouldCreateChildrenOnPrepareElement) {
            if ($this->targetElement !== null && $this->count > 0) {
                while ($this->count > $this->lastChildIndex + 1) {
                    $this->addNewTargetElementInstance(++$this->lastChildIndex);
                }
            }
        }

        // Create a template that will also be prepared
        if ($this->shouldCreateTemplate) {
            $templateElement = $this->getTemplateElement();
            $this->add($templateElement);
        }

        parent::prepareElement($form);

        // The template element has been prepared, but we don't want it to be
        // rendered nor validated, so remove it from the list.
        if ($this->shouldCreateTemplate) {
            $this->remove($this->templatePlaceholder);
        }
    }

    /**
     * @return array
     * @throws \Zend\Form\Exception\InvalidArgumentException
     * @throws \Zend\Stdlib\Exception\InvalidArgumentException
     * @throws \Zend\Form\Exception\DomainException
     * @throws \Zend\Form\Exception\InvalidElementException
     */
    public function extract()
    {
        if ($this->object instanceof Traversable) {
            $this->object = ArrayUtils::iteratorToArray($this->object, false);
        }

        if (!is_array($this->object)) {
            return array();
        }

        $values = array();

        foreach ($this->object as $key => $value) {
            // If a hydrator is provided, our work here is done
            if ($this->hydrator) {
                $values[$key] = $this->hydrator->extract($value);
                continue;
            }

            // If the target element is a fieldset that can accept the provided value
            // we should clone it, inject the value and extract the data
            if ($this->targetElement instanceof FieldsetInterface) {
                if (! $this->targetElement->allowObjectBinding($value)) {
                    continue;
                }
                $targetElement = clone $this->targetElement;
                $targetElement->setObject($value);
                $values[$key] = $targetElement->extract();
                if (!$this->createNewObjects() && $this->has($key)) {
                    $this->get($key)->setObject($value);
                }
                continue;
            }

            // If the target element is a non-fieldset element, just use the value
            if ($this->targetElement instanceof ElementInterface) {
                $values[$key] = $value;
                if (!$this->createNewObjects() && $this->has($key)) {
                    $this->get($key)->setValue($value);
                }
                continue;
            }
        }

        return $values;
    }

    /**
     * Create a new instance of the target element
     *
     * @return ElementInterface
     */
    protected function createNewTargetElementInstance()
    {
        return clone $this->targetElement;
    }

    /**
     * Add a new instance of the target element
     *
     * @param string $name
     * @return ElementInterface
     * @throws Exception\DomainException
     */
    protected function addNewTargetElementInstance($name)
    {
        $this->shouldCreateChildrenOnPrepareElement = false;

        $elementOrFieldset = $this->createNewTargetElementInstance();
        $elementOrFieldset->setName($name);

        $this->add($elementOrFieldset);

        if (!$this->allowAdd && $this->count() > $this->count) {
            throw new Exception\DomainException(sprintf(
                'There are more elements than specified in the collection (%s). Either set the allow_add option ' .
                'to true, or re-submit the form.',
                get_class($this)
            ));
        }

        return $elementOrFieldset;
    }

    /**
     * Create a dummy template element
     *
     * @return null|ElementInterface|FieldsetInterface
     */
    protected function createTemplateElement()
    {
        if (!$this->shouldCreateTemplate) {
            return;
        }

        if ($this->templateElement) {
            return $this->templateElement;
        }

        $elementOrFieldset = $this->createNewTargetElementInstance();
        $elementOrFieldset->setName($this->templatePlaceholder);

        return $elementOrFieldset;
    }

    /**
     * Replaces the default template object of a sub element with the corresponding
     * real entity so that all properties are preserved.
     *
     * @return void
     */
    protected function replaceTemplateObjects()
    {
        $fieldsets = $this->getFieldsets();

        if (!count($fieldsets) || !$this->object) {
            return;
        }

        foreach ($fieldsets as $fieldset) {
            $i = $fieldset->getName();
            if (isset($this->object[$i])) {
                $fieldset->setObject($this->object[$i]);
            }
        }
    }
}
