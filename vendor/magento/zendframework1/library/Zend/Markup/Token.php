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
 * @package    Zend_Markup
 * @subpackage Parser
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Markup_TokenList
 */
#require_once 'Zend/Markup/TokenList.php';

/**
 * @category   Zend
 * @package    Zend_Markup
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Markup_Token
{
    const TYPE_NONE    = 'none';
    const TYPE_TAG     = 'tag';

    /**
     * Children of this token
     *
     * @var Zend_Markup_TokenList
     */
    protected $_children;

    /**
     * The complete tag
     *
     * @var string
     */
    protected $_tag;

    /**
     * The tag's type
     *
     * @var string
     */
    protected $_type;

    /**
     * Tag name
     *
     * @var string
     */
    protected $_name = '';

    /**
     * Tag attributes
     *
     * @var array
     */
    protected $_attributes = array();

    /**
     * The used tag stopper (empty when none is found)
     *
     * @var string
     */
    protected $_stopper = '';

    /**
     * The parent token
     *
     * @var Zend_Markup_Token
     */
    protected $_parent;


    /**
     * Construct the token
     *
     * @param  string $tag
     * @param  string $type
     * @param  string $name
     * @param  array $attributes
     * @param  Zend_Markup_Token $parent
     * @return void
     */
    public function __construct(
        $tag,
        $type,
        $name = '',
        array $attributes = array(),
        Zend_Markup_Token $parent = null
    ) {
        $this->_tag        = $tag;
        $this->_type       = $type;
        $this->_name       = $name;
        $this->_attributes = $attributes;
        $this->_parent     = $parent;
    }

    // accessors

    /**
     * Set the stopper
     *
     * @param string $stopper
     * @return Zend_Markup_Token
     */
    public function setStopper($stopper)
    {
        $this->_stopper = $stopper;

        return $this;
    }

    /**
     * Get the stopper
     *
     * @return string
     */
    public function getStopper()
    {
        return $this->_stopper;
    }

    /**
     * Get the token's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get the token's type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get the complete tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->_tag;
    }

    /**
     * Get an attribute
     *
     * @param string $name
     *
     * @return string
     */
    public function getAttribute($name)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }

    /**
     * Check if the token has an attribute
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->_attributes[$name]);
    }

    /**
     * Get all the attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * Add an attribute
     *
     * @return Zend_Markup_Token
     */
    public function addAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;

        return $this;
    }

    /**
     * Check if an attribute is empty
     *
     * @param string $name
     *
     * @return bool
     */
    public function attributeIsEmpty($name)
    {
        return empty($this->_attributes[$name]);
    }

    // functions for child/parent tokens

    /**
     * Add a child token
     *
     * @return void
     */
    public function addChild(Zend_Markup_Token $child)
    {
        $this->getChildren()->addChild($child);
    }

    /**
     * Set the children token list
     *
     * @param  Zend_Markup_TokenList $children
     * @return Zend_Markup_Token
     */
    public function setChildren(Zend_Markup_TokenList $children)
    {
        $this->_children = $children;
        return $this;
    }

    /**
     * Get the children for this token
     *
     * @return Zend_Markup_TokenList
     */
    public function getChildren()
    {
        if (null === $this->_children) {
            $this->setChildren(new Zend_Markup_TokenList());
        }
        return $this->_children;
    }

    /**
     * Does this token have any children
     *
     * @return bool
     */
    public function hasChildren()
    {
        return !empty($this->_children);
    }

    /**
     * Get the parent token (if any)
     *
     * @return Zend_Markup_Token
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Set a parent token
     *
     * @param  Zend_Markup_Token $parent
     * @return Zend_Markup_Token
     */
    public function setParent(Zend_Markup_Token $parent)
    {
        $this->_parent = $parent;
        return $this;
    }

    /**
     * Magic clone function
     *
     * @return void
     */
    public function __clone()
    {
        $this->_parent   = null;
        $this->_children = null;
        $this->_tag      = '';
    }
}
