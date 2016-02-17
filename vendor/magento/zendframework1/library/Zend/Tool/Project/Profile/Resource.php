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
 * @see Zend_Tool_Project_Profile_Resource_Container
 */
#require_once 'Zend/Tool/Project/Profile/Resource/Container.php';

/**
 * @see Zend_Tool_Project_Context_Repository
 */
#require_once 'Zend/Tool/Project/Context/Repository.php';

/**
 * This class is an iterator that will iterate only over enabled resources
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Profile_Resource extends Zend_Tool_Project_Profile_Resource_Container
{

    /**
     * @var Zend_Tool_Project_Profile
     */
    protected $_profile = null;

    /**
     * @var Zend_Tool_Project_Profile_Resource
     */
    protected $_parentResource = null;

    /**#@+
     * @var bool
     */
    protected $_deleted = false;
    protected $_enabled = true;
    /**#@-*/

    /**
     * @var Zend_Tool_Project_Context|string
     */
    protected $_context = null;

    /**
     * @var array
     */
    protected $_attributes = array();

    /**
     * @var bool
     */
    protected $_isContextInitialized = false;

    /**
     * __construct()
     *
     * @param string|Zend_Tool_Project_Context_Interface $context
     */
    public function __construct($context)
    {
        $this->setContext($context);
    }

    /**
     * setContext()
     *
     * @param string|Zend_Tool_Project_Context_Interface $context
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function setContext($context)
    {
        $this->_context = $context;
        return $this;
    }

    /**
     * getContext()
     *
     * @return Zend_Tool_Project_Context_Interface
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * getName() - Get the resource name
     *
     * Name is derived from the context name
     *
     * @return string
     */
    public function getName()
    {
        if (is_string($this->_context)) {
            return $this->_context;
        } elseif ($this->_context instanceof Zend_Tool_Project_Context_Interface) {
            return $this->_context->getName();
        } else {
            throw new Zend_Tool_Project_Exception('Invalid context in resource');
        }
    }

    /**
     * setProfile()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function setProfile(Zend_Tool_Project_Profile $profile)
    {
        $this->_profile = $profile;
        return $this;
    }

    /**
     * getProfile
     *
     * @return Zend_Tool_Project_Profile
     */
    public function getProfile()
    {
        return $this->_profile;
    }

    /**
     * getPersistentAttributes()
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        if (method_exists($this->_context, 'getPersistentAttributes')) {
            return $this->_context->getPersistentAttributes();
        }

        return array();
    }

    /**
     * setEnabled()
     *
     * @param bool $enabled
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function setEnabled($enabled = true)
    {
        // convert fuzzy types to bool
        $this->_enabled = (!in_array($enabled, array('false', 'disabled', 0, -1, false), true)) ? true : false;
        return $this;
    }

    /**
     * isEnabled()
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }

    /**
     * setDeleted()
     *
     * @param bool $deleted
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function setDeleted($deleted = true)
    {
        $this->_deleted = (bool) $deleted;
        return $this;
    }

    /**
     * isDeleted()
     *
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function isDeleted()
    {
        return $this->_deleted;
    }

    /**
     * initializeContext()
     *
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function initializeContext()
    {
        if ($this->_isContextInitialized) {
            return;
        }
        if (is_string($this->_context)) {
            $this->_context = Zend_Tool_Project_Context_Repository::getInstance()->getContext($this->_context);
        }

        if (method_exists($this->_context, 'setResource')) {
            $this->_context->setResource($this);
        }

        if (method_exists($this->_context, 'init')) {
            $this->_context->init();
        }

        $this->_isContextInitialized = true;
        return $this;
    }

    /**
     * __toString()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_context->getName();
    }

    /**
     * __call()
     *
     * @param string $method
     * @param array $arguments
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function __call($method, $arguments)
    {
        if (method_exists($this->_context, $method)) {
            if (!$this->isEnabled()) {
                $this->setEnabled(true);
            }
            return call_user_func_array(array($this->_context, $method), $arguments);
        } else {
            throw new Zend_Tool_Project_Profile_Exception('cannot call ' . $method);
        }
    }

}
