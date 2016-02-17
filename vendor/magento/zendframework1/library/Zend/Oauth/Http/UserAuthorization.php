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

/** Zend_Oauth_Http */
#require_once 'Zend/Oauth/Http.php';

/** Zend_Uri_Http */
#require_once 'Zend/Uri/Http.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth_Http_UserAuthorization extends Zend_Oauth_Http
{
    /**
     * Generate a redirect URL from the allowable parameters and configured
     * values.
     *
     * @return string
     */
    public function getUrl()
    {
        $params = $this->assembleParams();
        $uri    = Zend_Uri_Http::fromString($this->_consumer->getUserAuthorizationUrl());

        $uri->setQuery(
            $this->_httpUtility->toEncodedQueryString($params)
        );

        return $uri->getUri();
    }

    /**
     * Assemble all parameters for inclusion in a redirect URL.
     *
     * @return array
     */
    public function assembleParams()
    {
        $params = array(
            'oauth_token' => $this->_consumer->getLastRequestToken()->getToken(),
        );

        if (!Zend_Oauth_Client::$supportsRevisionA) {
            $callback = $this->_consumer->getCallbackUrl();
            if (!empty($callback)) {
                $params['oauth_callback'] = $callback;
            }
        }

        if (!empty($this->_parameters)) {
            $params = array_merge($params, $this->_parameters);
        }

        return $params;
    }
}
