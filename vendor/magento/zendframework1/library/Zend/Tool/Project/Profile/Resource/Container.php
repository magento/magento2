<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tool_Project_Profile_Resource_SearchConstraints
 */
#require_once 'Zend/Tool/Project/Profile/Resource/SearchConstraints.php';

/**
 * This class is an iterator that will iterate only over enabled resources
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Profile_Resource_Container implements RecursiveIterator, Countable
{

    /**
     * @var array
     */
    protected $_subResources = array();

    /**
     * @var int
     */
    protected $_position = 0;

    /**
     * @var bool
     */
    protected $_appendable = true;

    /**
     * @var array
     */
    protected $_attributes = array();

    /**
     * Finder method to be able to find resources by context name
     * and attributes.  Example usage:
     *
     * <code>
     *
     * </code>
     *
     * @param Zend_Tool_Project_Profile_Resource_SearchConstraints|string|array $searchParameters
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function search($matchSearchConstraints, $nonMatchSearchConstraints = null)
    {
        if (!$matchSearchConstraints instanceof Zend_Tool_Project_Profile_Resource_SearchConstraints) {
            $matchSearchConstraints = new Zend_Tool_Project_Profile_Resource_SearchConstraints($matchSearchConstraints);
        }

        $this->rewind();

        /**
         * @todo This should be re-written with better support for a filter iterator, its the way to go
         */

        if ($nonMatchSearchConstraints) {
            $filterIterator = new Zend_Tool_Project_Profile_Iterator_ContextFilter($this, array('denyNames' => $nonMatchSearchConstraints));
            $riIterator = new RecursiveIteratorIterator($filterIterator, RecursiveIteratorIterator::SELF_FIRST);
        } else {
            $riIterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
        }

        $foundResource     = false;
        $currentConstraint = $matchSearchConstraints->getConstraint();
        $foundDepth        = 0;

        foreach ($riIterator as $currentResource) {

            // if current depth is less than found depth, end
            if ($riIterator->getDepth() < $foundDepth) {
                break;
            }

            if (strtolower($currentResource->getName()) == strtolower($currentConstraint->name)) {

                $paramsMatch = true;

                // @todo check to ensure params match (perhaps)
                if (count($currentConstraint->params) > 0) {
                    $currentResourceAttributes = $currentResource->getAttributes();
                    if (!is_array($currentConstraint->params)) {
                        #require_once 'Zend/Tool/Project/Profile/Exception.php';
                        throw new Zend_Tool_Project_Profile_Exception('Search parameter specifics must be in the form of an array for key "'
                            . $currentConstraint->name .'"');
                    }
                    foreach ($currentConstraint->params as $paramName => $paramValue) {
                        if (!isset($currentResourceAttributes[$paramName]) || $currentResourceAttributes[$paramName] != $paramValue) {
                            $paramsMatch = false;
                            break;
                        }
                    }
                }

                if ($paramsMatch) {
                    $foundDepth = $riIterator->getDepth();

                    if (($currentConstraint = $matchSearchConstraints->getConstraint()) == null) {
                        $foundResource = $currentResource;
                        break;
                    }
                }

            }

        }

        return $foundResource;
    }

    /**
     * createResourceAt()
     *
     * @param array|Zend_Tool_Project_Profile_Resource_SearchConstraints $appendResourceOrSearchConstraints
     * @param string $context
     * @param array $attributes
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function createResourceAt($appendResourceOrSearchConstraints, $context, Array $attributes = array())
    {
        if (!$appendResourceOrSearchConstraints instanceof Zend_Tool_Project_Profile_Resource_Container) {
            if (($parentResource = $this->search($appendResourceOrSearchConstraints)) == false) {
                #require_once 'Zend/Tool/Project/Profile/Exception.php';
                throw new Zend_Tool_Project_Profile_Exception('No node was found to append to.');
            }
        } else {
            $parentResource = $appendResourceOrSearchConstraints;
        }

        return $parentResource->createResource($context, $attributes);
    }

    /**
     * createResource()
     *
     * Method to create a resource with a given context with specific attributes
     *
     * @param string $context
     * @param array $attributes
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function createResource($context, Array $attributes = array())
    {
        if (is_string($context)) {
            $contextRegistry = Zend_Tool_Project_Context_Repository::getInstance();
            if ($contextRegistry->hasContext($context)) {
                $context = $contextRegistry->getContext($context);
            } else {
                #require_once 'Zend/Tool/Project/Profile/Exception.php';
                throw new Zend_Tool_Project_Profile_Exception('Context by name ' . $context . ' was not found in the context registry.');
            }
        } elseif (!$context instanceof Zend_Tool_Project_Context_Interface) {
            #require_once 'Zend/Tool/Project/Profile/Exception.php';
            throw new Zend_Tool_Project_Profile_Exception('Context must be of type string or Zend_Tool_Project_Context_Interface.');
        }

        $newResource = new Zend_Tool_Project_Profile_Resource($context);

        if ($attributes) {
            $newResource->setAttributes($attributes);
        }

        /**
         * Interesting logic here:
         *
         * First set the parentResource (this will also be done inside append).  This will allow
         * the initialization routine to change the appendability of the parent resource.  This
         * is important to allow specific resources to be appendable by very specific sub-resources.
         */
        $newResource->setParentResource($this);
        $newResource->initializeContext();
        $this->append($newResource);

        return $newResource;
    }

    /**
     * setAttributes()
     *
     * persist the attributes if the resource will accept them
     *
     * @param array $attributes
     * @return Zend_Tool_Project_Profile_Resource_Container
     */
    public function setAttributes(Array $attributes)
    {
        foreach ($attributes as $attrName => $attrValue) {
            $setMethod = 'set' . $attrName;
            if (method_exists($this, $setMethod)) {
                $this->{$setMethod}($attrValue);
            } else {
                $this->setAttribute($attrName, $attrValue);
            }
        }
        return $this;
    }

    /**
     * getAttributes()
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * setAttribute()
     *
     * @param string $name
     * @param mixed $value
     * @return Zend_Tool_Project_Profile_Resource_Container
     */
    public function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;
        return $this;
    }

    /**
     * getAttribute()
     *
     * @param string $name
     * @return Zend_Tool_Project_Profile_Resource_Container
     */
    public function getAttribute($name)
    {
        return (array_key_exists($name, $this->_attributes)) ? $this->_attributes[$name] : null;
    }

    /**
     * hasAttribute()
     *
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    /**
     * setAppendable()
     *
     * @param bool $appendable
     * @return Zend_Tool_Project_Profile_Resource_Container
     */
    public function setAppendable($appendable)
    {
        $this->_appendable = (bool) $appendable;
        return $this;
    }

    /**
     * isAppendable()
     *
     * @return bool
     */
    public function isAppendable()
    {
        return $this->_appendable;
    }

    /**
     * setParentResource()
     *
     * @param Zend_Tool_Project_Profile_Resource_Container $parentResource
     * @return Zend_Tool_Project_Profile_Resource_Container
     */
    public function setParentResource(Zend_Tool_Project_Profile_Resource_Container $parentResource)
    {
        $this->_parentResource = $parentResource;
        return $this;
    }

    /**
     * getParentResource()
     *
     * @return Zend_Tool_Project_Profile_Resource_Container
     */
    public function getParentResource()
    {
        return $this->_parentResource;
    }

    /**
     * append()
     *
     * @param Zend_Tool_Project_Profile_Resource_Container $resource
     * @return Zend_Tool_Project_Profile_Resource_Container
     */
    public function append(Zend_Tool_Project_Profile_Resource_Container $resource)
    {
        if (!$this->isAppendable()) {
            throw new Exception('Resource by name ' . (string) $this . ' is not appendable');
        }
        array_push($this->_subResources, $resource);
        $resource->setParentResource($this);

        return $this;
    }

    /**
     * current() - required by RecursiveIterator
     *
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function current()
    {
        return current($this->_subResources);
    }

    /**
     * key() - required by RecursiveIterator
     *
     * @return int
     */
    public function key()
    {
        return key($this->_subResources);
    }

    /**
     * next() - required by RecursiveIterator
     *
     * @return bool
     */
    public function next()
    {
        return next($this->_subResources);
    }

    /**
     * rewind() - required by RecursiveIterator
     *
     * @return bool
     */
    public function rewind()
    {
        return reset($this->_subResources);
    }

    /**
     * valid() - - required by RecursiveIterator
     *
     * @return bool
     */
    public function valid()
    {
        return (bool) $this->current();
    }

    /**
     * hasChildren()
     *
     * @return bool
     */
    public function hasChildren()
    {
        return (count($this->_subResources > 0)) ? true : false;
    }

    /**
     * getChildren()
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->current();
    }

    /**
     * count()
     *
     * @return int
     */
    public function count()
    {
        return count($this->_subResources);
    }

    /**
     * __clone()
     *
     */
    public function __clone()
    {
        $this->rewind();
        foreach ($this->_subResources as $index => $resource) {
            $this->_subResources[$index] = clone $resource;
        }
    }

}
