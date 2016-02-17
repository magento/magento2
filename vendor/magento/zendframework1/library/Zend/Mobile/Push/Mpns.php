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

/** Zend_Mobile_Push_Message_Mpns **/
#require_once 'Zend/Mobile/Push/Message/Mpns.php';

/**
 * Mpns Push
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Mobile_Push_Mpns extends Zend_Mobile_Push_Abstract
{
    /**
     * Http Client
     *
     * @var Zend_Http_Client
     */
    protected $_httpClient;

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
     * @return Zend_Mobile_Push_Mpns
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
     * @throws Zend_Mobile_Push_Exception_DeviceQuotaExceeded
     * @throws Zend_Mobile_Push_Exception_InvalidPayload
     * @throws Zend_Mobile_Push_Exception_InvalidToken
     * @throws Zend_Mobile_Push_Exception_QuotaExceeded
     * @throws Zend_Mobile_Push_Exception_ServerUnavailable
     * @return boolean
     */
    public function send(Zend_Mobile_Push_Message_Abstract $message)
    {
        if (!$message->validate()) {
            throw new Zend_Mobile_Push_Exception('The message is not valid.');
        }

        $this->connect();

        $client = $this->getHttpClient();
        $client->setUri($message->getToken());
        $client->setHeaders(array(
            'Context-Type' => 'text/xml',
            'Accept' => 'application/*',
            'X-NotificationClass' => $message->getDelay()
        ));
        if ($message->getId()) {
            $client->setHeaders('X-MessageID', $message->getId());
        }
        if ($message->getNotificationType() != Zend_Mobile_Push_Message_Mpns::TYPE_RAW) {
            $client->setHeaders('X-WindowsPhone-Target', $message->getNotificationType());
        }
        $client->setRawData($message->getXmlPayload(), 'text/xml');
        $response = $client->request('POST');
        $this->close();


        switch ($response->getStatus())
        {
            case 200:
                // check headers for response?  need to test how this actually works to correctly handle different states.
                if ($response->getHeader('NotificationStatus') == 'QueueFull') {
                    #require_once 'Zend/Mobile/Push/Exception/DeviceQuotaExceeded.php';
                    throw new Zend_Mobile_Push_Exception_DeviceQuotaExceeded('The devices push notification queue is full, use exponential backoff');
                }
                break;
            case 400:
                #require_once 'Zend/Mobile/Push/Exception/InvalidPayload.php';
                throw new Zend_Mobile_Push_Exception_InvalidPayload('The message xml was invalid');
                break;
            case 401:
                #require_once 'Zend/Mobile/Push/Exception/InvalidToken.php';
                throw new Zend_Mobile_Push_Exception_InvalidToken('The device token is not valid or there is a mismatch between certificates');
                break;
            case 404:
                #require_once 'Zend/Mobile/Push/Exception/InvalidToken.php';
                throw new Zend_Mobile_Push_Exception_InvalidToken('The device subscription is invalid, stop sending notifications to this device');
                break;
            case 405:
                throw new Zend_Mobile_Push_Exception('Invalid method, only POST is allowed'); // will never be hit unless overwritten
                break;
            case 406:
                #require_once 'Zend/Mobile/Push/Exception/QuotaExceeded.php';
                throw new Zend_Mobile_Push_Exception_QuotaExceeded('The unauthenticated web service has reached the per-day throttling limit');
                break;
            case 412:
                #require_once 'Zend/Mobile/Push/Exception/InvalidToken.php';
                throw new Zend_Mobile_Push_Exception_InvalidToken('The device is in an inactive state.  You may retry once per hour');
                break;
            case 503:
                #require_once 'Zend/Mobile/Push/Exception/ServerUnavailable.php';
                throw new Zend_Mobile_Push_Exception_ServerUnavailable('The server was unavailable.');
                break;
            default:
                break;
        }
        return true;
    }
}
