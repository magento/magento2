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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework;

interface UrlInterface
{
    /**#@+
     * Possible URL types
     */
    const URL_TYPE_LINK = 'link';
    const URL_TYPE_DIRECT_LINK = 'direct_link';
    const URL_TYPE_WEB = 'web';
    const URL_TYPE_MEDIA = 'media';
    const URL_TYPE_STATIC = 'static';
    const URL_TYPE_JS = 'js';
    /**#@-*/

    /**
     * Default url type
     *
     * Equals to self::URL_TYPE_LINK
     */
    const DEFAULT_URL_TYPE = 'link';

    /**
     * Default controller name
     */
    const DEFAULT_CONTROLLER_NAME = 'index';

    /**
     * Default action name
     */
    const DEFAULT_ACTION_NAME = 'index';

    /**
     * Rewrite request path alias
     */
    const REWRITE_REQUEST_PATH_ALIAS = 'rewrite_request_path';

    /**
     * Session namespace to refer in other places
     */
    const SESSION_NAMESPACE = 'frontend';

    /**
     * Retrieve use session rule
     *
     * @return bool
     */
    public function getUseSession();

    /**
     * Retrieve Base URL
     *
     * @param array $params
     * @return string
     */
    public function getBaseUrl($params = array());

    /**
     * Retrieve current url with port number (if not default used)
     *
     * @return string
     */
    public function getCurrentUrl();

    /**
     * Retrieve route URL
     *
     * @param string $routePath
     * @param array $routeParams
     * @return string
     */
    public function getRouteUrl($routePath = null, $routeParams = null);

    /**
     * Add session param
     *
     * @return \Magento\Framework\UrlInterface
     */
    public function addSessionParam();

    /**
     * Add query parameters
     *
     * @param array $data
     * @return \Magento\Framework\UrlInterface
     */
    public function addQueryParams(array $data);

    /**
     * Set query param
     *
     * @param string $key
     * @param mixed $data
     * @return \Magento\Framework\UrlInterface
     */
    public function setQueryParam($key, $data);

    /**
     * Build url by requested path and parameters
     *
     * @param   string|null $routePath
     * @param   array|null $routeParams
     * @return  string
     */
    public function getUrl($routePath = null, $routeParams = null);

    /**
     * Escape (enclosure) URL string
     *
     * @param string $value
     * @return string
     */
    public function escape($value);

    /**
     * Build url by direct url and parameters
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function getDirectUrl($url, $params = array());

    /**
     * Replace Session ID value in URL
     *
     * @param string $html
     * @return string
     */
    public function sessionUrlVar($html);

    /**
     * Check if users originated URL is one of the domain URLs assigned to stores
     *
     * @return boolean
     */
    public function isOwnOriginUrl();

    /**
     * Return frontend redirect URL with SID and other session parameters if any
     *
     * @param string $url
     *
     * @return string
     */
    public function getRedirectUrl($url);

    /**
     * Set scope entity
     *
     * @param mixed $params
     * @return \Magento\Framework\UrlInterface
     */
    public function setScope($params);
}
