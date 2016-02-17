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
 * This class is an iterator that will iterate only over enabled resources
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Profile_Iterator_ContextFilter extends RecursiveFilterIterator
{

    /**
     * @var array
     */
    protected $_acceptTypes = array();

    /**
     * @var array
     */
    protected $_denyTypes   = array();

    /**
     * @var array
     */
    protected $_acceptNames = array();

    /**
     * @var array
     */
    protected $_denyNames   = array();

    /**
     * @var array
     */
    protected $_rawOptions = array();

    /**
     * __construct()
     *
     * @param RecursiveIterator $iterator
     * @param array $options
     */
    public function __construct(RecursiveIterator $iterator, $options = array())
    {
        parent::__construct($iterator);
        $this->_rawOptions = $options;
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * setOptions()
     *
     * @param array $options
     */
    public function setOptions(Array $options)
    {
        foreach ($options as $optionName => $optionValue) {
            if (substr($optionName, -1, 1) != 's') {
                $optionName .= 's';
            }
            if (method_exists($this, 'set' . $optionName)) {
                $this->{'set' . $optionName}($optionValue);
            }
        }
    }

    /**
     * setAcceptTypes()
     *
     * @param array|string $acceptTypes
     * @return Zend_Tool_Project_Profile_Iterator_ContextFilter
     */
    public function setAcceptTypes($acceptTypes)
    {
        if (!is_array($acceptTypes)) {
            $acceptTypes = array($acceptTypes);
        }

        $this->_acceptTypes = $acceptTypes;
        return $this;
    }

    /**
     * setDenyTypes()
     *
     * @param array|string $denyTypes
     * @return Zend_Tool_Project_Profile_Iterator_ContextFilter
     */
    public function setDenyTypes($denyTypes)
    {
        if (!is_array($denyTypes)) {
            $denyTypes = array($denyTypes);
        }

        $this->_denyTypes = $denyTypes;
        return $this;
    }

    /**
     * setAcceptNames()
     *
     * @param array|string $acceptNames
     * @return Zend_Tool_Project_Profile_Iterator_ContextFilter
     */
    public function setAcceptNames($acceptNames)
    {
        if (!is_array($acceptNames)) {
            $acceptNames = array($acceptNames);
        }

        foreach ($acceptNames as $n => $v) {
            $acceptNames[$n] = strtolower($v);
        }

        $this->_acceptNames = $acceptNames;
        return $this;
    }

    /**
     * setDenyNames()
     *
     * @param array|string $denyNames
     * @return Zend_Tool_Project_Profile_Iterator_ContextFilter
     */
    public function setDenyNames($denyNames)
    {
        if (!is_array($denyNames)) {
            $denyNames = array($denyNames);
        }

        foreach ($denyNames as $n => $v) {
            $denyNames[$n] = strtolower($v);
        }

        $this->_denyNames = $denyNames;
        return $this;
    }

    /**
     * accept() is required by teh RecursiveFilterIterator
     *
     * @return bool
     */
    public function accept()
    {
        $currentItem = $this->current();

        if (in_array(strtolower($currentItem->getName()), $this->_acceptNames)) {
            return true;
        } elseif (in_array(strtolower($currentItem->getName()), $this->_denyNames)) {
            return false;
        }

        foreach ($this->_acceptTypes as $acceptType) {
            if ($currentItem->getContent() instanceof $acceptType) {
                return true;
            }
        }

        foreach ($this->_denyTypes as $denyType) {
            if ($currentItem->getContext() instanceof $denyType) {
                return false;
            }
        }

        return true;
    }

    /**
     * getChildren()
     *
     * This is here due to a bug/design issue in PHP
     * @link
     *
     * @return unknown
     */
    function getChildren()
    {

        if (empty($this->ref)) {
            $this->ref = new ReflectionClass($this);
        }

        return $this->ref->newInstance($this->getInnerIterator()->getChildren(), $this->_rawOptions);
    }

}
