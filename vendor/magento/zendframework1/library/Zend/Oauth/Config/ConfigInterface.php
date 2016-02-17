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
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Oauth_Config_ConfigInterface
{
    public function setOptions(array $options);

    public function setConsumerKey($key);

    public function getConsumerKey();

    public function setConsumerSecret($secret);

    public function getConsumerSecret();

    public function setSignatureMethod($method);

    public function getSignatureMethod();

    public function setRequestScheme($scheme);

    public function getRequestScheme();

    public function setVersion($version);

    public function getVersion();

    public function setCallbackUrl($url);

    public function getCallbackUrl();

    public function setRequestTokenUrl($url);

    public function getRequestTokenUrl();

    public function setRequestMethod($method);

    public function getRequestMethod();

    public function setAccessTokenUrl($url);

    public function getAccessTokenUrl();

    public function setUserAuthorizationUrl($url);

    public function getUserAuthorizationUrl();

    public function setToken(Zend_Oauth_Token $token);

    public function getToken();

    public function setRealm($realm);

    public function getRealm();
}
