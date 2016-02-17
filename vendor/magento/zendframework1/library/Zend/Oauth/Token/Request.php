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

/** Zend_Oauth_Token */
#require_once 'Zend/Oauth/Token.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth_Token_Request extends Zend_Oauth_Token
{
    /**
     * Constructor
     *
     * @param null|Zend_Http_Response $response
     * @param null|Zend_Oauth_Http_Utility $utility
     */
    public function __construct(
        Zend_Http_Response $response = null,
        Zend_Oauth_Http_Utility $utility = null
    ) {
        parent::__construct($response, $utility);

        // detect if server supports OAuth 1.0a
        if (isset($this->_params[Zend_Oauth_Token::TOKEN_PARAM_CALLBACK_CONFIRMED])) {
            Zend_Oauth_Client::$supportsRevisionA = true;
        }
    }
}
