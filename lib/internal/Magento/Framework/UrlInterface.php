<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * @api
 * @since 2.0.0
 */
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getUseSession();

    /**
     * Retrieve Base URL
     *
     * @param array $params
     * @return string
     * @since 2.0.0
     */
    public function getBaseUrl($params = []);

    /**
     * Retrieve current url with port number (if not default used)
     *
     * @return string
     * @since 2.0.0
     */
    public function getCurrentUrl();

    /**
     * Retrieve route URL
     *
     * @param string $routePath
     * @param array $routeParams
     * @return string
     * @since 2.0.0
     */
    public function getRouteUrl($routePath = null, $routeParams = null);

    /**
     * Add session param
     *
     * @return \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    public function addSessionParam();

    /**
     * Add query parameters
     *
     * @param array $data
     * @return \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    public function addQueryParams(array $data);

    /**
     * Set query param
     *
     * @param string $key
     * @param mixed $data
     * @return \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    public function setQueryParam($key, $data);

    /**
     * Build url by requested path and parameters
     *
     * @param   string|null $routePath
     * @param   array|null $routeParams
     * @return  string
     * @since 2.0.0
     */
    public function getUrl($routePath = null, $routeParams = null);

    /**
     * Escape (enclosure) URL string
     *
     * @param string $value
     * @return string
     * @since 2.0.0
     */
    public function escape($value);

    /**
     * Build url by direct url and parameters
     *
     * @param string $url
     * @param array $params
     * @return string
     * @since 2.0.0
     */
    public function getDirectUrl($url, $params = []);

    /**
     * Replace Session ID value in URL
     *
     * @param string $html
     * @return string
     * @since 2.0.0
     */
    public function sessionUrlVar($html);

    /**
     * Check if users originated URL is one of the domain URLs assigned to stores
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isOwnOriginUrl();

    /**
     * Return frontend redirect URL with SID and other session parameters if any
     *
     * @param string $url
     *
     * @return string
     * @since 2.0.0
     */
    public function getRedirectUrl($url);

    /**
     * Set scope entity
     *
     * @param mixed $params
     * @return \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    public function setScope($params);
}
