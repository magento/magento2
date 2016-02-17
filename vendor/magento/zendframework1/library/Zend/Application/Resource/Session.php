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
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
#require_once 'Zend/Application/Resource/ResourceAbstract.php';


/**
 * Resource for setting session options
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Application_Resource_Session extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Save handler to use
     *
     * @var Zend_Session_SaveHandler_Interface
     */
    protected $_saveHandler = null;

    /**
     * Set session save handler
     *
     * @param  array|string|Zend_Session_SaveHandler_Interface $saveHandler
     * @return Zend_Application_Resource_Session
     * @throws Zend_Application_Resource_Exception When $saveHandler is not a valid save handler
     */
    public function setSaveHandler($saveHandler)
    {
        $this->_saveHandler = $saveHandler;
        return $this;
    }

    /**
     * Get session save handler
     *
     * @return Zend_Session_SaveHandler_Interface
     * @throws Zend_Application_Resource_Exception
     */
    public function getSaveHandler()
    {
        if (!$this->_saveHandler instanceof Zend_Session_SaveHandler_Interface) {
            if (is_array($this->_saveHandler)) {
                if (!array_key_exists('class', $this->_saveHandler)) {
                    throw new Zend_Application_Resource_Exception('Session save handler class not provided in options');
                }
                $options = array();
                if (array_key_exists('options', $this->_saveHandler)) {
                    $options = $this->_saveHandler['options'];
                }
                $this->_saveHandler = $this->_saveHandler['class'];
                $this->_saveHandler = new $this->_saveHandler($options);
            } elseif (is_string($this->_saveHandler)) {
                $this->_saveHandler = new $this->_saveHandler();
            }

            if (!$this->_saveHandler instanceof Zend_Session_SaveHandler_Interface) {
                throw new Zend_Application_Resource_Exception('Invalid session save handler');
            }
        }
        return $this->_saveHandler;
    }

    /**
     * @return bool
     */
    protected function _hasSaveHandler()
    {
        return ($this->_saveHandler !== null);
    }

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return void
     */
    public function init()
    {
        $options = array_change_key_case($this->getOptions(), CASE_LOWER);
        if (isset($options['savehandler'])) {
            unset($options['savehandler']);
        }

        if (count($options) > 0) {
            Zend_Session::setOptions($options);
        }

        if ($this->_hasSaveHandler()) {
            Zend_Session::setSaveHandler($this->getSaveHandler());
        }
    }
}
