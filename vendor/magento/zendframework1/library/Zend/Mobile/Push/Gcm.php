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
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Http_Client **/
#require_once 'Zend/Http/Client.php';

/** Zend_Mobile_Push_Abstract **/
#require_once 'Zend/Mobile/Push/Abstract.php';

/** Zend_Mobile_Push_Message_Gcm **/
#require_once 'Zend/Mobile/Push/Message/Gcm.php';

/** Zend_Mobile_Push_Response_Gcm **/
#require_once 'Zend/Mobile/Push/Response/Gcm.php';

/**
 * GCM Push
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Mobile_Push_Gcm extends Zend_Mobile_Push_Abstract
{

    /**
     * @const string Server URI
     */
    const SERVER_URI = 'https://android.googleapis.com/gcm/send';

    /**
     * Http Client
     *
     * @var Zend_Http_Client
     */
    protected $_httpClient;

    /**
     * API Key
     *
     * @var string
     */
    protected $_apiKey;

    /**
     * Get API Key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }

    /**
     * Set API Key
     *
     * @param  string $key
     * @return Zend_Mobile_Push_Gcm
     * @throws Zend_Mobile_Push_Exception
     */
    public function setApiKey($key)
    {
        if (!is_string($key) || empty($key)) {
            throw new Zend_Mobile_Push_Exception('The api key must be a string and not empty');
        }
        $this->_apiKey = $key;
        return $this;
    }

    /**
     * Get Http Client
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if (!$this->_httpClient) {
            $this->_httpClient = new Zend_Http_Client();
            $this->_httpClient->setConfig(array(
                'strictredirects' => true,
            ));
        }
        return $this->_httpClient;
    }

    /**
     * Set Http Client
     *
     * @return Zend_Mobile_Push_Gcm
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->_httpClient = $client;
        return $this;
    }

    /**
     * Send Message
     *
     * @param  Zend_Mobile_Push_Message_Abstract $message
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Mobile_Push_Exception
     * @throws Zend_Mobile_Push_Exception_InvalidAuthToken
     * @throws Zend_Mobile_Push_Exception_InvalidPayload
     * @throws Zend_Mobile_Push_Exception_ServerUnavailable
     * @return Zend_Mobile_Push_Response_Gcm
     */
    public function send(Zend_Mobile_Push_Message_Abstract $message)
    {
        if (!$message->validate()) {
            throw new Zend_Mobile_Push_Exception('The message is not valid.');
        }

        $this->connect();

        $client = $this->getHttpClient();
        $client->setUri(self::SERVER_URI);
        $client->setHeaders('Authorization', 'key=' . $this->getApiKey());

        $response = $client->setRawData($message->toJson(), 'application/json')
                           ->request('POST');
        $this->close();

        switch ($response->getStatus())
        {
            case 500:
                #require_once 'Zend/Mobile/Push/Exception/ServerUnavailable.php';
                throw new Zend_Mobile_Push_Exception_ServerUnavailable('The server encountered an internal error, try again');
                break;
            case 503:
                #require_once 'Zend/Mobile/Push/Exception/ServerUnavailable.php';
                throw new Zend_Mobile_Push_Exception_ServerUnavailable('The server was unavailable, check Retry-After header');
                break;
            case 401:
                #require_once 'Zend/Mobile/Push/Exception/InvalidAuthToken.php';
                throw new Zend_Mobile_Push_Exception_InvalidAuthToken('There was an error authenticating the sender account');
                break;
            case 400:
                #require_once 'Zend/Mobile/Push/Exception/InvalidPayload.php';
                throw new Zend_Mobile_Push_Exception_InvalidPayload('The request could not be parsed as JSON or contains invalid fields');
                break;
        }
        return new Zend_Mobile_Push_Response_Gcm($response->getBody(), $message);
    }
}
