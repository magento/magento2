<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Server
 */

namespace Zend\Server\Reflection;

/**
 * Node Tree class for Zend_Server reflection operations
 *
 * @category   Zend
 * @package    Zend_Server
 * @subpackage Zend_Server_Reflection
 */
class Node
{
    /**
     * Node value
     * @var mixed
     */
    protected $value = null;

    /**
     * Array of child nodes (if any)
     * @var array
     */
    protected $children = array();

    /**
     * Parent node (if any)
     * @var \Zend\Server\Reflection\Node
     */
    protected $parent = null;

    /**
     * Constructor
     *
     * @param mixed $value
     * @param \Zend\Server\Reflection\Node $parent Optional
     * @return \Zend\Server\Reflection\Node
     */
    public function __construct($value, Node $parent = null)
    {
        $this->value = $value;
        if (null !== $parent) {
            $this->setParent($parent, true);
        }

        return $this;
    }

    /**
     * Set parent node
     *
     * @param \Zend\Server\Reflection\Node $node
     * @param boolean $new Whether or not the child node is newly created
     * and should always be attached
     * @return void
     */
    public function setParent(Node $node, $new = false)
    {
        $this->parent = $node;

        if ($new) {
            $node->attachChild($this);
            return;
        }
    }

    /**
     * Create and attach a new child node
     *
     * @param mixed $value
     * @access public
     * @return \Zend\Server\Reflection\Node New child node
     */
    public function createChild($value)
    {
        $child = new self($value, $this);

        return $child;
    }

    /**
     * Attach a child node
     *
     * @param \Zend\Server\Reflection\Node $node
     * @return void
     */
    public function attachChild(Node $node)
    {
        $this->children[] = $node;

        if ($node->getParent() !== $this) {
            $node->setParent($this);
        }
    }

    /**
     * Return an array of all child nodes
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Does this node have children?
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return count($this->children) > 0;
    }

    /**
     * Return the parent node
     *
     * @return null|\Zend\Server\Reflection\Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Return the node's current value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the node value
     *
     * @param mixed $value
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Retrieve the bottommost nodes of this node's tree
     *
     * Retrieves the bottommost nodes of the tree by recursively calling
     * getEndPoints() on all children. If a child is null, it returns the parent
     * as an end point.
     *
     * @return array
     */
    public function getEndPoints()
    {
        $endPoints = array();
        if (!$this->hasChildren()) {
            return $endPoints;
        }

        foreach ($this->children as $child) {
            $value = $child->getValue();

            if (null === $value) {
                $endPoints[] = $this;
            } elseif ((null !== $value)
                && $child->hasChildren())
            {
                $childEndPoints = $child->getEndPoints();
                if (!empty($childEndPoints)) {
                    $endPoints = array_merge($endPoints, $childEndPoints);
                }
            } elseif ((null !== $value) && !$child->hasChildren()) {
                $endPoints[] = $child;
            }
        }

        return $endPoints;
    }
}
