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

/** Zend_Mobile_Push_Abstract **/
#require_once 'Zend/Mobile/Push/Abstract.php';

/** Zend_Mobile_Push_Message_Apns **/
#require_once 'Zend/Mobile/Push/Message/Apns.php';

/**
 * APNS Push
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Mobile_Push_Apns extends Zend_Mobile_Push_Abstract
{

    /**
     * @const int apple server uri constants
     */
    const SERVER_SANDBOX_URI = 0;
    const SERVER_PRODUCTION_URI = 1;
    const SERVER_FEEDBACK_SANDBOX_URI = 2;
    const SERVER_FEEDBACK_PRODUCTION_URI = 3;

    /**
     * Apple Server URI's
     *
     * @var array
     */
    protected $_serverUriList = array(
        'ssl://gateway.sandbox.push.apple.com:2195',
        'ssl://gateway.push.apple.com:2195',
        'ssl://feedback.sandbox.push.apple.com:2196',
        'ssl://feedback.push.apple.com:2196'
    );

    /**
     * Current Environment
     *
     * @var int
     */
    protected $_currentEnv;

    /**
     * Socket
     *
     * @var resource
     */
    protected $_socket;

    /**
     * Certificate
     *
     * @var string
     */
    protected $_certificate;

    /**
     * Certificate Passphrase
     *
     * @var string
     */
    protected $_certificatePassphrase;

    /**
     * Get Certficiate
     *
     * @return string
     */
    public function getCertificate()
    {
        return $this->_certificate;
    }

    /**
     * Set Certificate
     *
     * @param  string $cert
     * @return Zend_Mobile_Push_Apns
     * @throws Zend_Mobile_Push_Exception
     */
    public function setCertificate($cert)
    {
        if (!is_string($cert)) {
            throw new Zend_Mobile_Push_Exception('$cert must be a string');
        }
        if (!file_exists($cert)) {
            throw new Zend_Mobile_Push_Exception('$cert must be a valid path to the certificate');
        }
        $this->_certificate = $cert;
        return $this;
    }

    /**
     * Get Certificate Passphrase
     *
     * @return string
     */
    public function getCertificatePassphrase()
    {
        return $this->_certificatePassphrase;
    }

    /**
     * Set Certificate Passphrase
     *
     * @param  string $passphrase
     * @return Zend_Mobile_Push_Apns
     * @throws Zend_Mobile_Push_Exception
     */
    public function setCertificatePassphrase($passphrase)
    {
        if (!is_string($passphrase)) {
            throw new Zend_Mobile_Push_Exception('$passphrase must be a string');
        }
        $this->_certificatePassphrase = $passphrase;
        return $this;
    }

    /**
     * Connect to Socket
     *
     * @param  string $uri
     * @return bool
     * @throws Zend_Mobile_Push_Exception_ServerUnavailable
     */
    protected function _connect($uri)
    {
        $ssl = array(
            'local_cert' => $this->_certificate,
        );
        if ($this->_certificatePassphrase) {
            $ssl['passphrase'] = $this->_certificatePassphrase;
        }

        $this->_socket = stream_socket_client($uri,
            $errno,
            $errstr,
            ini_get('default_socket_timeout'),
            STREAM_CLIENT_CONNECT,
            stream_context_create(array(
                'ssl' => $ssl,
            ))
        );

        if (!is_resource($this->_socket)) {
            #require_once 'Zend/Mobile/Push/Exception/ServerUnavailable.php';
            throw new Zend_Mobile_Push_Exception_ServerUnavailable(sprintf('Unable to connect: %s: %d (%s)',
                $uri,
                $errno,
                $errstr
            ));
        }

        stream_set_blocking($this->_socket, 0);
        stream_set_write_buffer($this->_socket, 0);
        return true;
    }

    /**
    * Read from the Socket Server
    *
    * @param int $length
    * @return string
    */
    protected function _read($length) {
        $data = false;
        if (!feof($this->_socket)) {
            $data = fread($this->_socket, $length);
        }
        return $data;
    }

    /**
    * Write to the Socket Server
    *
    * @param string $payload
    * @return int
    */
    protected function _write($payload) {
        return @fwrite($this->_socket, $payload);
    }

    /**
     * Connect to the Push Server
     *
     * @param  int|string $env
     * @throws Zend_Mobile_Push_Exception
     * @throws Zend_Mobile_Push_Exception_ServerUnavailable
     * @return Zend_Mobile_Push_Abstract
     */
    public function connect($env = self::SERVER_PRODUCTION_URI)
    {
        if ($this->_isConnected) {
            if ($this->_currentEnv == self::SERVER_PRODUCTION_URI) {
                return $this;
            }
            $this->close();
        }

        if (!isset($this->_serverUriList[$env])) {
            throw new Zend_Mobile_Push_Exception('$env is not a valid environment');
        }

        if (!$this->_certificate) {
            throw new Zend_Mobile_Push_Exception('A certificate must be set prior to calling ::connect');
        }

        $this->_connect($this->_serverUriList[$env]);

        $this->_currentEnv = $env;
        $this->_isConnected = true;
        return $this;
    }



    /**
     * Feedback
     *
     * @return array array w/ key = token and value = time
     * @throws Zend_Mobile_Push_Exception
     * @throws Zend_Mobile_Push_Exception_ServerUnavailable
     */
    public function feedback()
    {
        if (!$this->_isConnected ||
            !in_array($this->_currentEnv,
                array(self::SERVER_FEEDBACK_SANDBOX_URI, self::SERVER_FEEDBACK_PRODUCTION_URI))) {
            $this->connect(self::SERVER_FEEDBACK_PRODUCTION_URI);
        }

        $tokens = array();
        while ($token = $this->_read(38)) {
            if (strlen($token) < 38) {
                continue;
            }
            $token = unpack('Ntime/ntokenLength/H*token', $token);
            if (!isset($tokens[$token['token']]) || $tokens[$token['token']] < $token['time']) {
                $tokens[$token['token']] = $token['time'];
            }
        }
        return $tokens;
    }

    /**
     * Send Message
     *
     * @param  Zend_Mobile_Push_Message_Abstract $message
     * @throws Zend_Mobile_Push_Exception
     * @throws Zend_Mobile_Push_Exception_InvalidPayload
     * @throws Zend_Mobile_Push_Exception_InvalidToken
     * @throws Zend_Mobile_Push_Exception_InvalidTopic
     * @throws Zend_Mobile_Push_Exception_ServerUnavailable
     * @return bool
     */
    public function send(Zend_Mobile_Push_Message_Abstract $message)
    {
        if (!$message->validate()) {
            throw new Zend_Mobile_Push_Exception('The message is not valid.');
        }

        if (!$this->_isConnected || !in_array($this->_currentEnv, array(
            self::SERVER_SANDBOX_URI,
            self::SERVER_PRODUCTION_URI))) {
            $this->connect(self::SERVER_PRODUCTION_URI);
        }

        $payload = array('aps' => array());

        $alert = $message->getAlert();
        foreach ($alert as $k => $v) {
            if ($v == null) {
                unset($alert[$k]);
            }
        }
        if (!empty($alert)) {
            $payload['aps']['alert'] = $alert;
        }
        if (!is_null($message->getBadge())) {
            $payload['aps']['badge'] = $message->getBadge();
        }
        $sound = $message->getSound();
        if (!empty($sound)) {
            $payload['aps']['sound'] = $sound;
        }

        foreach($message->getCustomData() as $k => $v) {
            $payload[$k] = $v;
        }

        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $payload = json_encode($payload);
        }

        $expire = $message->getExpire();
        if ($expire > 0) {
            $expire += time();
        }
        $id = $message->getId();
        if (empty($id)) {
            $id = time();
        }

        $payload = pack('CNNnH*', 1, $id, $expire, 32, $message->getToken())
            . pack('n', strlen($payload))
            . $payload;
        $ret = $this->_write($payload);
        if ($ret === false) {
            #require_once 'Zend/Mobile/Push/Exception/ServerUnavailable.php';
            throw new Zend_Mobile_Push_Exception_ServerUnavailable('Unable to send message');
        }
        // check for errors from apple
        $err = $this->_read(1024);
        if (strlen($err) > 0) {
            $err = unpack('Ccmd/Cerrno/Nid', $err);
            switch ($err['errno']) {
                case 0:
                    return true;
                    break;
                case 1:
                    throw new Zend_Mobile_Push_Exception('A processing error has occurred on the apple push notification server.');
                    break;
                case 2:
                    #require_once 'Zend/Mobile/Push/Exception/InvalidToken.php';
                    throw new Zend_Mobile_Push_Exception_InvalidToken('Missing token; you must set a token for the message.');
                    break;
                case 3:
                    #require_once 'Zend/Mobile/Push/Exception/InvalidTopic.php';
                    throw new Zend_Mobile_Push_Exception_InvalidTopic('Missing id; you must set an id for the message.');
                    break;
                case 4:
                    #require_once 'Zend/Mobile/Push/Exception/InvalidPayload.php';
                    throw new Zend_Mobile_Push_Exception_InvalidPayload('Missing message; the message must always have some content.');
                    break;
                case 5:
                    #require_once 'Zend/Mobile/Push/Exception/InvalidToken.php';
                    throw new Zend_Mobile_Push_Exception_InvalidToken('Bad token.  This token is too big and is not a regular apns token.');
                    break;
                case 6:
                    #require_once 'Zend/Mobile/Push/Exception/InvalidTopic.php';
                    throw new Zend_Mobile_Push_Exception_InvalidTopic('The message id is too big; reduce the size of the id.');
                    break;
                case 7:
                    #require_once 'Zend/Mobile/Push/Exception/InvalidPayload.php';
                    throw new Zend_Mobile_Push_Exception_InvalidPayload('The message is too big; reduce the size of the message.');
                    break;
                case 8:
                    #require_once 'Zend/Mobile/Push/Exception/InvalidToken.php';
                    throw new Zend_Mobile_Push_Exception_InvalidToken('Bad token.  Remove this token from being sent to again.');
                    break;
                default:
                    throw new Zend_Mobile_Push_Exception(sprintf('An unknown error occurred: %d', $err['errno']));
                    break;
            }
        }
        return true;
    }

    /**
     * Close Connection
     *
     * @return void
     */
    public function close()
    {
        if ($this->_isConnected && is_resource($this->_socket)) {
            fclose($this->_socket);
        }
        $this->_isConnected = false;
    }
}
