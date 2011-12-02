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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SearchConstraints.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * This class is an iterator that will iterate only over enabled resources
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Profile_Resource_SearchConstraints
{

    /**
     * @var array
     */
    protected $_constraints = array();

    /**
     * __construct()
     *
     * @param array|string $options
     */
    public function __construct($options = null)
    {
        if (is_string($options)) {
            $this->addConstraint($options);
        } elseif (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * setOptions()
     *
     * @param array $option
     * @return Zend_Tool_Project_Profile_Resource_SearchConstraints
     */
    public function setOptions(Array $option)
    {
        foreach ($option as $optionName => $optionValue) {
            if (is_int($optionName)) {
                $this->addConstraint($optionValue);
            } elseif (is_string($optionName)) {
                $this->addConstraint(array('name' => $optionName, 'params' => $optionValue));
            }
        }

        return $this;
    }

    /**
     * addConstraint()
     *
     * @param string|array $constraint
     * @return Zend_Tool_Project_Profile_Resource_SearchConstraints
     */
    public function addConstraint($constraint)
    {
        if (is_string($constraint)) {
            $name   = $constraint;
            $params = array();
        } elseif (is_array($constraint)) {
            $name   = $constraint['name'];
            $params = $constraint['params'];
        }

        $constraint = $this->_makeConstraint($name, $params);

        array_push($this->_constraints, $constraint);
        return $this;
    }

    /**
     * getConstraint()
     *
     * @return ArrayObject
     */
    public function getConstraint()
    {
        return array_shift($this->_constraints);
    }

    /**
     * _makeConstraint
     *
     * @param string $name
     * @param mixed $params
     * @return ArrayObject
     */
    protected function _makeConstraint($name, $params)
    {
        $value = array('name' => $name, 'params' => $params);
        return new ArrayObject($value, ArrayObject::ARRAY_AS_PROPS);
    }

}