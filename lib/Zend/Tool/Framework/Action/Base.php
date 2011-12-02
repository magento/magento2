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
 * @version    $Id: Base.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Tool_Framework_Action_Interface
 */
#require_once 'Zend/Tool/Framework/Action/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Action_Base implements Zend_Tool_Framework_Action_Interface
{

    /**
     * @var string
     */
    protected $_name = null;

    /**
     * constructor -
     *
     * @param unknown_type $options
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            if (is_string($options)) {
                $this->setName($options);
            }
            // implement $options here in the future if this is needed
        }
    }

    /**
     * setName()
     *
     * @param string $name
     * @return Zend_Tool_Framework_Action_Base
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        if ($this->_name == null) {
            $this->_name = $this->_parseName();
        }
        return $this->_name;
    }

    /**
     * _parseName - internal method to determine the name of an action when one is not explicity provided.
     *
     * @param Zend_Tool_Framework_Action_Interface $action
     * @return string
     */
    protected function _parseName()
    {
        $className = get_class($this);
        $actionName = substr($className, strrpos($className, '_')+1);
        return $actionName;
    }

}
