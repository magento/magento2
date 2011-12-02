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
 * @package     Mage_HTTP
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Interface for different HTTP clients
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
interface Mage_HTTP_IClient
{
    /**
     * Set request timeout
     * @param int $value
     */
    function setTimeout($value);
    
    
    /**
     * Set request headers from hash
     * @param array $headers
     */
    function setHeaders($headers);
    
    /**
     * Add header to request 
     * @param string $name
     * @param string $value
     */
    function addHeader($name, $value);
    
    
    /**
     * Remove header from request
     * @param string $name
     */
    function removeHeader($name);


    /**
     * Set login credentials
     * for basic auth.
     * @param string $login
     * @param string $pass
     */
    function setCredentials($login, $pass);
    
    /**
     * Add cookie to request 
     * @param string $name
     * @param string $value
     */
    function addCookie($name, $value);

    /**
     * Remove cookie from request
     * @param string $name
     */
    function removeCookie($name);
    
    /**
     * Set request cookies from hash
     * @param array $cookies
     */ 
    function setCookies($cookies);

    /**
     * Remove cookies from request
     */
    function removeCookies();

    /**
     * Make GET request
     * @param string full uri
     */
    function get($uri);

    /**
     * Make POST request
     * @param string $uri full uri
     * @param array $params POST fields array
     */ 
    function post($uri, $params);
    
    /**
     * Get response headers
     * @return array
     */ 
    function getHeaders();
    
    /**
     * Get response body
     * @return string
     */
    function getBody(); 
    
    /**
     * Get response status code
     * @return int
     */
    function getStatus();
    
    /**
     * Get response cookies (k=>v) 
     * @return array
     */
    function getCookies();
    
    /**
     * Set additional option
     * @param string $key
     * @param string $value
     */
    function setOption($key, $value);

    /**
     * Set additional options
     * @param array $arr
     */
    function setOptions($arr);
}
