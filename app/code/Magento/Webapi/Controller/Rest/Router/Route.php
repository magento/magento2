<?php
/**
 * Route to services available via REST API.
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Controller\Rest\Router;

class Route extends \Zend_Controller_Router_Route
{
    /** @var string */
    protected $_serviceClass;

    /** @var string */
    protected $_serviceMethod;

    /** @var boolean */
    protected $_secure;

    /** @var array */
    protected $_aclResources = [];

    /** @var array */
    protected $_parameters = [];

    /**
     * Set service class.
     *
     * @param string $serviceClass
     * @return $this
     */
    public function setServiceClass($serviceClass)
    {
        $this->_serviceClass = $serviceClass;
        return $this;
    }

    /**
     * Get service class.
     *
     * @return string
     */
    public function getServiceClass()
    {
        return $this->_serviceClass;
    }

    /**
     * Set service method name.
     *
     * @param string $serviceMethod
     * @return $this
     */
    public function setServiceMethod($serviceMethod)
    {
        $this->_serviceMethod = $serviceMethod;
        return $this;
    }

    /**
     * Get service method name.
     *
     * @return string
     */
    public function getServiceMethod()
    {
        return $this->_serviceMethod;
    }

    /**
     * Set if the route is secure
     *
     * @param boolean $secure
     * @return $this
     */
    public function setSecure($secure)
    {
        $this->_secure = $secure;
        return $this;
    }

    /**
     * Returns true if the route is secure
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->_secure;
    }

    /**
     * Set ACL resources list.
     *
     * @param array $aclResources
     * @return $this
     */
    public function setAclResources($aclResources)
    {
        $this->_aclResources = $aclResources;
        return $this;
    }

    /**
     * Get ACL resources list.
     *
     * @return array
     */
    public function getAclResources()
    {
        return $this->_aclResources;
    }


    /**
     * Set parameters list.
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->_parameters = $parameters;
        return $this;
    }

    /**
     * Get parameters list.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Matches a Request with parts defined by a map. Assigns and
     * returns an array of variables on a successful match.
     *
     * @param \Magento\Webapi\Controller\Request $request
     * @param boolean $partial Partial path matching
     * @return array|bool An array of assigned values or a boolean false on a mismatch
     */
    public function match($request, $partial = false)
    {
        return parent::match(strtolower(ltrim($request->getPathInfo(), $this->_urlDelimiter)), $partial);
    }
}
