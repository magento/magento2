<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Annotation;

use Zend\EventManager\EventManagerInterface;

/**
 * Default listeners for form annotations
 *
 * Defines and attaches a set of default listeners for form annotations
 * (which are defined on object properties). These include:
 *
 * - Attributes
 * - Flags
 * - Hydrator
 * - Object and Instance (the latter is preferred starting in 2.4)
 * - InputFilter
 * - Type
 * - ValidationGroup
 *
 * See the individual annotation classes for more details. The handlers
 * registered work with the annotation values, as well as the form
 * specification passed in the event object.
 */
class FormAnnotationsListener extends AbstractAnnotationsListener
{
    /**
     * Attach listeners
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('configureForm', array($this, 'handleAttributesAnnotation'));
        $this->listeners[] = $events->attach('configureForm', array($this, 'handleFlagsAnnotation'));
        $this->listeners[] = $events->attach('configureForm', array($this, 'handleHydratorAnnotation'));
        $this->listeners[] = $events->attach('configureForm', array($this, 'handleInputFilterAnnotation'));
        $this->listeners[] = $events->attach('configureForm', array($this, 'handleObjectAnnotation'));
        $this->listeners[] = $events->attach('configureForm', array($this, 'handleOptionsAnnotation'));
        $this->listeners[] = $events->attach('configureForm', array($this, 'handleTypeAnnotation'));
        $this->listeners[] = $events->attach('configureForm', array($this, 'handleValidationGroupAnnotation'));

        $this->listeners[] = $events->attach('discoverName', array($this, 'handleNameAnnotation'));
        $this->listeners[] = $events->attach('discoverName', array($this, 'discoverFallbackName'));
    }

    /**
     * Handle the Attributes annotation
     *
     * Sets the attributes key of the form specification.
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return void
     */
    public function handleAttributesAnnotation($e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof Attributes) {
            return;
        }

        $formSpec = $e->getParam('formSpec');
        $formSpec['attributes'] = $annotation->getAttributes();
    }

    /**
     * Handle the Flags annotation
     *
     * Sets the flags key of the form specification.
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return void
     */
    public function handleFlagsAnnotation($e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof Flags) {
            return;
        }

        $formSpec = $e->getParam('formSpec');
        $formSpec['flags'] = $annotation->getFlags();
    }

    /**
     * Handle the Hydrator annotation
     *
     * Sets the hydrator class to use in the form specification.
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return void
     */
    public function handleHydratorAnnotation($e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof Hydrator) {
            return;
        }

        $formSpec = $e->getParam('formSpec');
        $formSpec['hydrator'] = $annotation->getHydrator();
    }

    /**
     * Handle the InputFilter annotation
     *
     * Sets the input filter class to use in the form specification.
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return void
     */
    public function handleInputFilterAnnotation($e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof InputFilter) {
            return;
        }

        $formSpec = $e->getParam('formSpec');
        $formSpec['input_filter'] = $annotation->getInputFilter();
    }

    /**
     * Handle the Object and Instance annotations
     *
     * Sets the object to bind to the form or fieldset
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return void
     */
    public function handleObjectAnnotation($e)
    {
        $annotation = $e->getParam('annotation');

        // Only need to typehint on Instance, as Object extends it
        if (! $annotation instanceof Instance) {
            return;
        }

        $formSpec = $e->getParam('formSpec');
        $formSpec['object'] = $annotation->getObject();
    }

    /**
     * Handle the Options annotation
     *
     * Sets the options key of the form specification.
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return void
     */
    public function handleOptionsAnnotation($e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof Options) {
            return;
        }

        $formSpec = $e->getParam('formSpec');
        $formSpec['options'] = $annotation->getOptions();
    }

    /**
     * Handle the Type annotation
     *
     * Sets the form class to use in the form specification.
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return void
     */
    public function handleTypeAnnotation($e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof Type) {
            return;
        }

        $formSpec = $e->getParam('formSpec');
        $formSpec['type'] = $annotation->getType();
    }

    /**
     * Handle the ValidationGroup annotation
     *
     * Sets the validation group to use in the form specification.
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return void
     */
    public function handleValidationGroupAnnotation($e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof ValidationGroup) {
            return;
        }

        $formSpec = $e->getParam('formSpec');
        $formSpec['validation_group'] = $annotation->getValidationGroup();
    }
}
