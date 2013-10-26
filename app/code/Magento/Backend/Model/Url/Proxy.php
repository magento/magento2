<?php
/**
 * Proxy class for \Magento\Backend\Model\Url
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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
namespace Magento\Backend\Model\Url;

/**
* @SuppressWarnings(PHPMD.ExcessivePublicCount)
*/
class Proxy extends \Magento\Backend\Model\Url
{
    /**
     * Object Manager instance
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager = null;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_object;

    /**
     * Proxy constructor
     *
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get instance
     *
     * @return \Magento\Backend\Model\Url
     */
    protected function _getObject()
    {
        if (null === $this->_object) {
            $this->_object = $this->_objectManager->get('Magento\Backend\Model\Url');
        }
        return $this->_object;
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure()
    {
        return $this->_getObject()->isSecure();
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteParams(array $data, $unsetOldParams = true)
    {
        return $this->_getObject()->setRouteParams($data, $unsetOldParams);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        return $this->_getObject()->getUrl($routePath, $routeParams);
    }

    /**
     * {@inheritdoc}
     */
    public function getSecretKey($routeName = null, $controller = null, $action = null)
    {
        return $this->_getObject()->getSecretKey($routeName, $controller, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function useSecretKey()
    {
        return $this->_getObject()->useSecretKey();
    }

    /**
     * {@inheritdoc}
     */
    public function turnOnSecretKey()
    {
        return $this->_getObject()->turnOnSecretKey();
    }

    /**
     * {@inheritdoc}
     */
    public function turnOffSecretKey()
    {
        return $this->_getObject()->turnOffSecretKey();
    }

    /**
     * {@inheritdoc}
     */
    public function renewSecretUrls()
    {
        return $this->_getObject()->renewSecretUrls();
    }

    /**
     * {@inheritdoc}
     */
    public function getStartupPageUrl()
    {
        return $this->_getObject()->getStartupPageUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function findFirstAvailableMenu()
    {
        return $this->_getObject()->findFirstAvailableMenu();
    }

    /**
     * {@inheritdoc}
     */
    public function setSession(\Magento\Backend\Model\Auth\Session $session)
    {
        return $this->_getObject()->setSession($session);
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaFrontName()
    {
        return $this->_getObject()->getAreaFrontName();
    }

    /**
     * {@inheritdoc}
     */
    public function getActionPath()
    {
        return $this->_getObject()->getActionPath();
    }

    /**
     * {@inheritdoc}
     */
    public function parseUrl($url)
    {
        return $this->_getObject()->parseUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultControllerName()
    {
        return $this->_getObject()->getDefaultControllerName();
    }

    /**
     * {@inheritdoc}
     */
    public function setUseUrlCache($flag)
    {
        return $this->_getObject()->setUseUrlCache($flag);
    }

    /**
     * {@inheritdoc}
     */
    public function setUseSession($useSession)
    {
        return $this->_getObject()->setUseSession($useSession);
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteFrontName($name)
    {
        return $this->_getObject()->setRouteFrontName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getUseSession()
    {
        return $this->_getObject()->getUseSession();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultActionName()
    {
        return $this->_getObject()->getDefaultActionName();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigData($key, $prefix = null)
    {
        return $this->_getObject()->getConfigData($key, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(\Magento\App\RequestInterface $request)
    {
        return $this->_getObject()->setRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest()
    {
        return $this->_getObject()->getRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->_getObject()->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function setStore($params)
    {
        return $this->_getObject()->setStore($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getStore()
    {
        return $this->_getObject()->getStore();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl($params = array())
    {
        return $this->_getObject()->getBaseUrl($params);
    }

    /**
     * {@inheritdoc}
     */
    public function setRoutePath($data)
    {
        return $this->_getObject()->setRoutePath($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutePath($routeParams = array())
    {
        return $this->_getObject()->getRoutePath($routeParams);
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteName($data)
    {
        return $this->_getObject()->setRouteName($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteFrontName()
    {
        return $this->_getObject()->getRouteFrontName();
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName($default = null)
    {
        return $this->_getObject()->getRouteName($default);
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerName($data)
    {
        return $this->_getObject()->setControllerName($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerName($default = null)
    {
        return $this->_getObject()->getControllerName($default);
    }

    /**
     * {@inheritdoc}
     */
    public function setActionName($data)
    {
        return $this->_getObject()->setActionName($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionName($default = null)
    {
        return $this->_getObject()->getActionName($default);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParams()
    {
        return $this->_getObject()->getRouteParams();
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteParam($key, $data)
    {
        return $this->_getObject()->setRouteParam($key, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParam($key)
    {
        return $this->_getObject()->getRouteParam($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteUrl($routePath = null, $routeParams = null)
    {
        return $this->_getObject()->getRouteUrl($routePath, $routeParams);
    }

    /**
     * {@inheritdoc}
     */
    public function checkCookieDomains()
    {
        return $this->_getObject()->checkCookieDomains();
    }

    /**
     * {@inheritdoc}
     */
    public function addSessionParam()
    {
        return $this->_getObject()->addSessionParam();
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery($data)
    {
        return $this->_getObject()->setQuery($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery($escape = false)
    {
        return $this->_getObject()->getQuery($escape);
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryParams(array $data)
    {
        return $this->_getObject()->setQueryParams($data);
    }

    /**
     * {@inheritdoc}
     */
    public function purgeQueryParams()
    {
        return $this->_getObject()->purgeQueryParams();
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams()
    {
        return $this->_getObject()->getQueryParams();
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryParam($key, $data)
    {
        return $this->_getObject()->setQueryParam($key, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParam($key)
    {
        return $this->_getObject()->getQueryParam($key);
    }

    /**
     * {@inheritdoc}
     */
    public function setFragment($data)
    {
        return $this->_getObject()->setFragment($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->_getObject()->getFragment();
    }

    /**
     * {@inheritdoc}
     */
    public function getRebuiltUrl($url)
    {
        return $this->_getObject()->getRebuiltUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function escape($value)
    {
        return $this->_getObject()->escape($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectUrl($url, $params = array())
    {
        return $this->_getObject()->getDirectUrl($url, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function sessionUrlVar($html)
    {
        return $this->_getObject()->sessionUrlVar($html);
    }

    /**
     * {@inheritdoc}
     */
    public function useSessionIdForUrl($secure = false)
    {
        return $this->_getObject()->useSessionIdForUrl($secure);
    }

    /**
     * {@inheritdoc}
     */
    public function sessionVarCallback($match)
    {
        return $this->_getObject()->sessionVarCallback($match);
    }

    /**
     * {@inheritdoc}
     */
    public function isOwnOriginUrl()
    {
        return $this->_getObject()->isOwnOriginUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUrl($url)
    {
        return $this->_getObject()->getRedirectUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function isDeleted($isDeleted = null)
    {
        return $this->_getObject()->isDeleted($isDeleted);
    }

    /**
     * {@inheritdoc}
     */
    public function hasDataChanges()
    {
        return $this->_getObject()->hasDataChanges();
    }

    /**
     * {@inheritdoc}
     */
    public function setIdFieldName($name)
    {
        return $this->_getObject()->setIdFieldName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdFieldName()
    {
        return $this->_getObject()->getIdFieldName();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_getObject()->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function setId($value)
    {
        return $this->_getObject()->setId($value);
    }

    /**
     * {@inheritdoc}
     */
    public function addData(array $arr)
    {
        return $this->_getObject()->addData($arr);
    }

    /**
     * {@inheritdoc}
     */
    public function setData($key, $value = null)
    {
        return $this->_getObject()->setData($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetData($key = null)
    {
        return $this->_getObject()->unsetData($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key = '', $index = null)
    {
        return $this->_getObject()->getData($key, $index);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataByPath($path)
    {
        return $this->_getObject()->getDataByPath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataByKey($key)
    {
        return $this->_getObject()->getDataByKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function setDataUsingMethod($key, $args = array())
    {
        return $this->_getObject()->setDataUsingMethod($key, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataUsingMethod($key, $args = null)
    {
        return $this->_getObject()->getDataUsingMethod($key, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSetDefault($key, $default)
    {
        return $this->_getObject()->getDataSetDefault($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function hasData($key = '')
    {
        return $this->_getObject()->hasData($key);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(array $keys = array())
    {
        return $this->_getObject()->toArray($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToArray(array $keys = array())
    {
        return $this->_getObject()->convertToArray($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function toXml(array $keys = array(), $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        return $this->_getObject()->toXml($keys, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToXml(
        array $arrAttributes = array(),
        $rootName = 'item',
        $addOpenTag = false,
        $addCdata = true
    ) {
        return $this->_getObject()->convertToXml($arrAttributes, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * {@inheritdoc}
     */
    public function toJson(array $keys = array())
    {
        return $this->_getObject()->toJson($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToJson(array $keys = array())
    {
        return $this->_getObject()->convertToJson($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function toString($format = '')
    {
        return $this->_getObject()->toString($format);
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $args)
    {
        return $this->_getObject()->__call($method, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return $this->_getObject()->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($keys = array(), $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"')
    {
        return $this->_getObject()->serialize($keys, $valueSeparator, $fieldSeparator, $quote);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrigData($key = null, $data = null)
    {
        return $this->_getObject()->setOrigData($key, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrigData($key = null)
    {
        return $this->_getObject()->getOrigData($key);
    }

    /**
     * {@inheritdoc}
     */
    public function dataHasChangedFor($field)
    {
        return $this->_getObject()->dataHasChangedFor($field);
    }

    /**
     * {@inheritdoc}
     */
    public function setDataChanges($value)
    {
        return $this->_getObject()->setDataChanges($value);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($data = null, &$objects = array())
    {
        return $this->_getObject()->debug($data, $objects);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->_getObject()->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->_getObject()->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->_getObject()->offsetUnset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->_getObject()->offsetGet($offset);
    }
}
