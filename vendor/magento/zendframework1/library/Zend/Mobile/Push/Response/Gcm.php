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

/**
 * Gcm Response
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Mobile_Push_Response_Gcm
{

    const RESULT_MESSAGE_ID = 'message_id';
    const RESULT_ERROR = 'error';
    const RESULT_CANONICAL = 'registration_id';

    /**
     * Multicast ID
     * @var int
     */
    protected $_id;

    /**
     * Success Count
     * @var int
     */
    protected $_successCnt;

    /**
     * Failure Count
     * @var int
     */
    protected $_failureCnt;

    /**
     * Canonical registration id count
     * @var int
     */
    protected $_canonicalCnt;

    /**
     * Message
     * @var Zend_Mobile_Push_Message_Gcm
     */
    protected $_message;

    /**
     * Results
     * @var array
     */
    protected $_results;

    /**
     * Raw Response
     * @var array
     */
    protected $_response;

    /**
     * Constructor
     *
     * @param string $responseString JSON encoded response
     * @param Zend_Mobile_Push_Message_Gcm $message
     * @return Zend_Mobile_Push_Response_Gcm
     * @throws Zend_Mobile_Push_Exception_ServerUnavailable
     */
    public function __construct($responseString = null, Zend_Mobile_Push_Message_Gcm $message = null)
    {
        if ($responseString) {
            if (!$response = json_decode($responseString, true)) {
                #require_once 'Zend/Mobile/Push/Exception/ServerUnavailable.php';
                throw new Zend_Mobile_Push_Exception_ServerUnavailable('The server gave us an invalid response, try again later');
            }
            $this->setResponse($response);
        }

        if ($message) {
            $this->setMessage($message);
        }

    }

    /**
     * Get Message
     *
     * @return Zend_Mobile_Push_Message_Gcm
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Set Message
     *
     * @param Zend_Mobile_Push_Message_Gcm $message
     * @return Zend_Mobile_Push_Response_Gcm
     */
    public function setMessage(Zend_Mobile_Push_Message_Gcm $message)
    {
        $this->_message = $message;
        return $this;
    }

    /**
     * Get Response
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Set Response
     *
     * @param  array $response
     * @throws Zend_Mobile_Push_Exception
     * @return Zend_Mobile_Push_Response_Gcm
     */
    public function setResponse(array $response)
    {
        if (!isset($response['results']) ||
            !isset($response['success']) ||
            !isset($response['failure']) ||
            !isset($response['canonical_ids']) ||
            !isset($response['multicast_id'])) {
            throw new Zend_Mobile_Push_Exception('Response did not contain the proper fields');
        }
        $this->_response = $response;
        $this->_results = $response['results'];
        $this->_successCnt = (int) $response['success'];
        $this->_failureCnt = (int) $response['failure'];
        $this->_canonicalCnt = (int) $response['canonical_ids'];
        $this->_id = (int) $response['multicast_id'];
        return $this;
    }

    /**
     * Get Success Count
     *
     * @return int
     */
    public function getSuccessCount()
    {
        return $this->_successCnt;
    }

    /**
     * Get Failure Count
     *
     * @return int
     */
    public function getFailureCount()
    {
        return $this->_failureCnt;
    }

    /**
     * Get Canonical Count
     *
     * @return int
     */
    public function getCanonicalCount()
    {
        return $this->_canonicalCnt;
    }

    /**
     * Get Results
     *
     * @return array multi dimensional array of:
     *         NOTE: key is registration_id if the message is passed.
     *         'registration_id' => array(
     *             'message_id' => 'id',
     *             'error' => 'error',
     *             'registration_id' => 'id'
     *          )
     */
    public function getResults()
    {
        return $this->_correlate();
    }

    /**
     * Get Singular Result
     *
     * @param  int   $flag one of the RESULT_* flags
     * @return array singular array with keys being registration id
     *               value is the type of result
     */
    public function getResult($flag)
    {
        $ret = array();
        foreach ($this->_correlate() as $k => $v) {
            if (isset($v[$flag])) {
                $ret[$k] = $v[$flag];
            }
        }
        return $ret;
    }

    /**
     * Correlate Message and Result
     *
     * @return array
     */
    protected function _correlate()
    {
        $results = $this->_results;
        if ($this->_message && $results) {
            $tokens = $this->_message->getToken();
            while($token = array_shift($tokens)) {
                $results[$token] = array_shift($results);
            }
        }
        return $results;
    }
}
