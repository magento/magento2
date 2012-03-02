<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Controller exception that can fork different actions, cause forward or redirect
 *
 */
class Mage_Core_Controller_Varien_Exception extends Exception
{
    const RESULT_FORWARD  = '_forward';
    const RESULT_REDIRECT = '_redirect';

    protected $_resultCallback       = null;
    protected $_resultCallbackParams = array();
    protected $_defaultActionName    = 'noroute';
    protected $_flags                = array();

    /**
     * Prepare data for forwarding action
     *
     * @param string $actionName
     * @param string $controllerName
     * @param string $moduleName
     * @param array $params
     * @return Mage_Core_Controller_Varien_Exception
     */
    public function prepareForward($actionName = null, $controllerName = null, $moduleName = null, array $params = array())
    {
        $this->_resultCallback = self::RESULT_FORWARD;
        if (null === $actionName) {
            $actionName = $this->_defaultActionName;
        }
        $this->_resultCallbackParams = array($actionName, $controllerName, $moduleName, $params);
        return $this;
    }

    /**
     * Prepare data for redirecting
     *
     * @param string $path
     * @param array $arguments
     * @return Mage_Core_Controller_Varien_Exception
     */
    public function prepareRedirect($path, $arguments = array())
    {
        $this->_resultCallback = self::RESULT_REDIRECT;
        $this->_resultCallbackParams($path, $arguments);
        return $this;
    }

    /**
     * Prepare data for running a custom action
     *
     * @param string $actionName
     * @return Mage_Core_Controller_Varien_Exception
     */
    public function prepareFork($actionName = null)
    {
        if (null === $actionName) {
            $actionName = $this->_defaultActionName;
        }
        $this->_resultCallback = $actionName;
        return $this;
    }

    /**
     * Prepare a flag data
     *
     * @param string $action
     * @param string $flag
     * @param bool $value
     * @return Mage_Core_Controller_Varien_Exception
     */
    public function prepareFlag($action, $flag, $value)
    {
        $this->_flags[] = array($action, $flag, $value);
        return $this;
    }

    /**
     * Return all set flags
     *
     * @return array
     */
    public function getResultFlags()
    {
        return $this->_flags;
    }

    /**
     * Return results as callback for a controller
     *
     * @return array
     */
    public function getResultCallback()
    {
        if (null === $this->_resultCallback) {
            $this->prepareFork();
        }
        return array($this->_resultCallback, $this->_resultCallbackParams);
    }
}
