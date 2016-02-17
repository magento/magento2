<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form;

use Countable;
use IteratorAggregate;
use Zend\Stdlib\Hydrator\HydratorInterface;

interface FieldsetInterface extends
    Countable,
    IteratorAggregate,
    ElementInterface,
    ElementPrepareAwareInterface,
    FormFactoryAwareInterface
{
    /**
     * Add an element or fieldset
     *
     * $flags could contain metadata such as the alias under which to register
     * the element or fieldset, order in which to prioritize it, etc.
     *
     * @param  array|\Traversable|ElementInterface $elementOrFieldset Typically, only allow objects implementing ElementInterface;
     *                                                                however, keeping it flexible to allow a factory-based form
     *                                                                implementation as well
     * @param  array $flags
     * @return FieldsetInterface
     */
    public function add($elementOrFieldset, array $flags = array());

    /**
     * Does the fieldset have an element/fieldset by the given name?
     *
     * @param  string $elementOrFieldset
     * @return bool
     */
    public function has($elementOrFieldset);

    /**
     * Retrieve a named element or fieldset
     *
     * @param  string $elementOrFieldset
     * @return ElementInterface
     */
    public function get($elementOrFieldset);

    /**
     * Remove a named element or fieldset
     *
     * @param  string $elementOrFieldset
     * @return FieldsetInterface
     */
    public function remove($elementOrFieldset);

    /**
     * Set/change the priority of an element or fieldset
     *
     * @param string $elementOrFieldset
     * @param int $priority
     * @return FieldsetInterface
     */
    public function setPriority($elementOrFieldset, $priority);

    /**
     * Retrieve all attached elements
     *
     * Storage is an implementation detail of the concrete class.
     *
     * @return array|\Traversable
     */
    public function getElements();

    /**
     * Retrieve all attached fieldsets
     *
     * Storage is an implementation detail of the concrete class.
     *
     * @return array|\Traversable
     */
    public function getFieldsets();

    /**
     * Recursively populate value attributes of elements
     *
     * @param  array|\Traversable $data
     * @return void
     */
    public function populateValues($data);

    /**
     * Set the object used by the hydrator
     *
     * @param  $object
     * @return FieldsetInterface
     */
    public function setObject($object);

    /**
     * Get the object used by the hydrator
     *
     * @return mixed
     */
    public function getObject();

    /**
     * Checks if the object can be set in this fieldset
     *
     * @param $object
     * @return bool
     */
    public function allowObjectBinding($object);

    /**
     * Set the hydrator to use when binding an object to the element
     *
     * @param  HydratorInterface $hydrator
     * @return FieldsetInterface
     */
    public function setHydrator(HydratorInterface $hydrator);

    /**
     * Get the hydrator used when binding an object to the element
     *
     * @return null|HydratorInterface
     */
    public function getHydrator();

    /**
     * Bind values to the bound object
     *
     * @param  array $values
     * @return mixed
     */
    public function bindValues(array $values = array());

    /**
     * Checks if this fieldset can bind data
     *
     * @return bool
     */
    public function allowValueBinding();
}
