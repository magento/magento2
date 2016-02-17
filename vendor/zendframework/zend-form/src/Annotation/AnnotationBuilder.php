<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Annotation;

use ArrayObject;
use Zend\Code\Annotation\AnnotationCollection;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser;
use Zend\Code\Reflection\ClassReflection;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Form\Exception;
use Zend\Form\Factory;
use Zend\Form\FormFactoryAwareInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Parses the properties of a class for annotations in order to create a form
 * and input filter definition.
 */
class AnnotationBuilder implements EventManagerAwareInterface, FormFactoryAwareInterface
{
    /**
     * @var Parser\DoctrineAnnotationParser
     */
    protected $annotationParser;

    /**
     * @var AnnotationManager
     */
    protected $annotationManager;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var Factory
     */
    protected $formFactory;

    /**
     * @var object
     */
    protected $entity;

    /**
     * @var array Default annotations to register
     */
    protected $defaultAnnotations = array(
        'AllowEmpty',
        'Attributes',
        'ComposedObject',
        'ContinueIfEmpty',
        'ErrorMessage',
        'Exclude',
        'Filter',
        'Flags',
        'Hydrator',
        'Input',
        'InputFilter',
        'Instance',
        'Name',
        'Object',
        'Options',
        'Required',
        'Type',
        'ValidationGroup',
        'Validator'
    );

    /**
     * @var bool
     */
    protected $preserveDefinedOrder = false;

    /**
     * Set form factory to use when building form from annotations
     *
     * @param  Factory $formFactory
     * @return AnnotationBuilder
     */
    public function setFormFactory(Factory $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * Set annotation manager to use when building form from annotations
     *
     * @param  AnnotationManager $annotationManager
     * @return AnnotationBuilder
     */
    public function setAnnotationManager(AnnotationManager $annotationManager)
    {
        $parser = $this->getAnnotationParser();
        foreach ($this->defaultAnnotations as $annotationName) {
            $class = __NAMESPACE__ . '\\' . $annotationName;
            $parser->registerAnnotation($class);
        }
        $annotationManager->attach($parser);
        $this->annotationManager = $annotationManager;
        return $this;
    }

    /**
     * Set event manager instance
     *
     * @param  EventManagerInterface $events
     * @return AnnotationBuilder
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_class($this),
        ));
        $events->attach(new ElementAnnotationsListener());
        $events->attach(new FormAnnotationsListener());
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve form factory
     *
     * Lazy-loads the default form factory if none is currently set.
     *
     * @return Factory
     */
    public function getFormFactory()
    {
        if ($this->formFactory) {
            return $this->formFactory;
        }

        $this->formFactory = new Factory();
        return $this->formFactory;
    }

    /**
     * Retrieve annotation manager
     *
     * If none is currently set, creates one with default annotations.
     *
     * @return AnnotationManager
     */
    public function getAnnotationManager()
    {
        if ($this->annotationManager) {
            return $this->annotationManager;
        }

        $this->setAnnotationManager(new AnnotationManager());
        return $this->annotationManager;
    }

    /**
     * Get event manager
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * Creates and returns a form specification for use with a factory
     *
     * Parses the object provided, and processes annotations for the class and
     * all properties. Information from annotations is then used to create
     * specifications for a form, its elements, and its input filter.
     *
     * @param  string|object $entity Either an instance or a valid class name for an entity
     * @throws Exception\InvalidArgumentException if $entity is not an object or class name
     * @return ArrayObject
     */
    public function getFormSpecification($entity)
    {
        if (!is_object($entity)) {
            if ((is_string($entity) && (!class_exists($entity))) // non-existent class
                || (!is_string($entity)) // not an object or string
            ) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '%s expects an object or valid class name; received "%s"',
                    __METHOD__,
                    var_export($entity, 1)
                ));
            }
        }

        $this->entity      = $entity;
        $annotationManager = $this->getAnnotationManager();
        $formSpec          = new ArrayObject();
        $filterSpec        = new ArrayObject();

        $reflection  = new ClassReflection($entity);
        $annotations = $reflection->getAnnotations($annotationManager);

        if ($annotations instanceof AnnotationCollection) {
            $this->configureForm($annotations, $reflection, $formSpec, $filterSpec);
        }

        foreach ($reflection->getProperties() as $property) {
            $annotations = $property->getAnnotations($annotationManager);

            if ($annotations instanceof AnnotationCollection) {
                $this->configureElement($annotations, $property, $formSpec, $filterSpec);
            }
        }

        if (!isset($formSpec['input_filter'])) {
            $formSpec['input_filter'] = $filterSpec;
        } elseif (is_array($formSpec['input_filter'])) {
            $formSpec['input_filter'] = ArrayUtils::merge($filterSpec->getArrayCopy(), $formSpec['input_filter']);
        }

        return $formSpec;
    }

    /**
     * Create a form from an object.
     *
     * @param  string|object $entity
     * @return \Zend\Form\Form
     */
    public function createForm($entity)
    {
        $formSpec    = ArrayUtils::iteratorToArray($this->getFormSpecification($entity));
        $formFactory = $this->getFormFactory();
        return $formFactory->createForm($formSpec);
    }

    /**
     * Get the entity used to construct the form.
     *
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Configure the form specification from annotations
     *
     * @param  AnnotationCollection $annotations
     * @param  ClassReflection $reflection
     * @param  ArrayObject $formSpec
     * @param  ArrayObject $filterSpec
     * @return void
     * @triggers discoverName
     * @triggers configureForm
     */
    protected function configureForm($annotations, $reflection, $formSpec, $filterSpec)
    {
        $name                   = $this->discoverName($annotations, $reflection);
        $formSpec['name']       = $name;
        $formSpec['attributes'] = array();
        $formSpec['elements']   = array();
        $formSpec['fieldsets']  = array();

        $events = $this->getEventManager();
        foreach ($annotations as $annotation) {
            $events->trigger(__FUNCTION__, $this, array(
                'annotation' => $annotation,
                'name'        => $name,
                'formSpec'   => $formSpec,
                'filterSpec' => $filterSpec,
            ));
        }
    }

    /**
     * Configure an element from annotations
     *
     * @param  AnnotationCollection $annotations
     * @param  \Zend\Code\Reflection\PropertyReflection $reflection
     * @param  ArrayObject $formSpec
     * @param  ArrayObject $filterSpec
     * @return void
     * @triggers checkForExclude
     * @triggers discoverName
     * @triggers configureElement
     */
    protected function configureElement($annotations, $reflection, $formSpec, $filterSpec)
    {
        // If the element is marked as exclude, return early
        if ($this->checkForExclude($annotations)) {
            return;
        }

        $events = $this->getEventManager();
        $name   = $this->discoverName($annotations, $reflection);

        $elementSpec = new ArrayObject(array(
            'flags' => array(),
            'spec'  => array(
                'name' => $name
            ),
        ));
        $inputSpec = new ArrayObject(array(
            'name' => $name,
        ));

        $event = new Event();
        $event->setParams(array(
            'name'        => $name,
            'elementSpec' => $elementSpec,
            'inputSpec'   => $inputSpec,
            'formSpec'    => $formSpec,
            'filterSpec'  => $filterSpec,
        ));
        foreach ($annotations as $annotation) {
            $event->setParam('annotation', $annotation);
            $events->trigger(__FUNCTION__, $this, $event);
        }

        // Since "type" is a reserved name in the filter specification,
        // we need to add the specification without the name as the key.
        // In all other cases, though, the name is fine.
        if ($event->getParam('inputSpec')->count() > 1) {
            if ($name === 'type') {
                $filterSpec[] = $event->getParam('inputSpec');
            } else {
                $filterSpec[$name] = $event->getParam('inputSpec');
            }
        }

        $elementSpec = $event->getParam('elementSpec');
        $type        = (isset($elementSpec['spec']['type']))
            ? $elementSpec['spec']['type']
            : 'Zend\Form\Element';

        // Compose as a fieldset or an element, based on specification type.
        // If preserve defined order is true, all elements are composed as elements to keep their ordering
        if (!$this->preserveDefinedOrder() && is_subclass_of($type, 'Zend\Form\FieldsetInterface')) {
            if (!isset($formSpec['fieldsets'])) {
                $formSpec['fieldsets'] = array();
            }
            $formSpec['fieldsets'][] = $elementSpec;
        } else {
            if (!isset($formSpec['elements'])) {
                $formSpec['elements'] = array();
            }
            $formSpec['elements'][] = $elementSpec;
        }
    }

    /**
     * @param bool $preserveDefinedOrder
     * @return $this
     */
    public function setPreserveDefinedOrder($preserveDefinedOrder)
    {
        $this->preserveDefinedOrder = (bool) $preserveDefinedOrder;
        return $this;
    }

    /**
     * @return bool
     */
    public function preserveDefinedOrder()
    {
        return $this->preserveDefinedOrder;
    }

    /**
     * Discover the name of the given form or element
     *
     * @param  AnnotationCollection $annotations
     * @param  \Reflector $reflection
     * @return string
     */
    protected function discoverName($annotations, $reflection)
    {
        $results = $this->getEventManager()->trigger('discoverName', $this, array(
            'annotations' => $annotations,
            'reflection'  => $reflection,
        ), function ($r) {
            return (is_string($r) && !empty($r));
        });
        return $results->last();
    }

    /**
     * Determine if an element is marked to exclude from the definitions
     *
     * @param  AnnotationCollection $annotations
     * @return true|false
     */
    protected function checkForExclude($annotations)
    {
        $results = $this->getEventManager()->trigger('checkForExclude', $this, array(
            'annotations' => $annotations,
        ), function ($r) {
            return (true === $r);
        });
        return (bool) $results->last();
    }

    /**
     * @return \Zend\Code\Annotation\Parser\DoctrineAnnotationParser
     */
    public function getAnnotationParser()
    {
        if (null === $this->annotationParser) {
            $this->annotationParser = new Parser\DoctrineAnnotationParser();
        }

        return $this->annotationParser;
    }

    /**
     * Checks if the object has this class as one of its parents
     *
     * @see https://bugs.php.net/bug.php?id=53727
     * @see https://github.com/zendframework/zf2/pull/1807
     *
     * @deprecated since zf 2.3 requires PHP >= 5.3.23
     *
     * @param string $className
     * @param string $type
     * @return bool
     */
    protected static function isSubclassOf($className, $type)
    {
        return is_subclass_of($className, $type);
    }
}
