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

/** Zend_Mobile_Push_Message_Abstract **/
#require_once 'Zend/Mobile/Push/Message/Abstract.php';

/**
 * Gcm Message
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 * @method     array getToken()
 */
class Zend_Mobile_Push_Message_Gcm extends Zend_Mobile_Push_Message_Abstract
{

    /**
     * Tokens
     *
     * @var array
     */
    protected $_token = array();

    /**
     * Data key value pairs
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Delay while idle
     *
     * @var boolean
     */
    protected $_delay = false;

    /**
     * Time to live in seconds
     *
     * @var int
     */
    protected $_ttl = 2419200;

    /**
     * Add a Token
     *
     * @param  string $token
     * @return Zend_Mobile_Push_Message_Gcm
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function addToken($token)
    {
        if (!is_string($token)) {
            throw new Zend_Mobile_Push_Message_Exception('$token must be a string');
        }
        if (!in_array($token, $this->_token)) {
           $this->_token[] = $token;
        }
        return $this;
    }

    /**
     * Set Token
     *
     * @param  string|array $token
     * @return Zend_Mobile_Push_Message_Gcm
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setToken($token)
    {
        $this->clearToken();
        if (is_string($token)) {
            $this->addToken($token);
        } else if (is_array($token)) {
            foreach ($token as $t) {
                $this->addToken($t);
            }
        }
        return $this;
    }

    /**
     * Clear Tokens
     *
     * @return Zend_Mobile_Push_Message_Gcm
     */
    public function clearToken()
    {
        $this->_token = array();
        return $this;
    }


    /**
     * Add Data
     *
     * @param  string $key
     * @param  string $value
     * @return Zend_Mobile_Push_Message_Gcm
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function addData($key, $value)
    {
        if (!is_string($key)) {
            throw new Zend_Mobile_Push_Message_Exception('$key is not a string');
        }
        if (!is_scalar($value)) {
            throw new Zend_Mobile_Push_Message_Exception('$value is not a string');
        }
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     * Set Data
     *
     * @param array $data
     * @return Zend_Mobile_Push_Message_Gcm
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setData(array $data)
    {
        $this->clearData();
        foreach ($data as $k => $v) {
            $this->addData($k, $v);
        }
        return $this;
    }

    /**
     * Clear Data
     *
     * @return Zend_Mobile_Push_Message_Gcm
     */
    public function clearData()
    {
        $this->_data = array();
        return $this;
    }

    /**
     * Get Data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Set Delay While Idle
     *
     * @param boolean $delay
     * @return Zend_Mobile_Push_Message_Gcm
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setDelayWhileIdle($delay)
    {
        if (!is_bool($delay)) {
            throw new Zend_Mobile_Push_Message_Exception('$delay must be boolean');
        }
        $this->_delay = $delay;
        return $this;
    }

    /**
     * Get Delay While Idle
     *
     * @return boolean
     */
    public function getDelayWhileIdle()
    {
        return $this->_delay;
    }

    /**
     * Set time to live.
     *
     * @param  int $secs
     * @throws Zend_Mobile_Push_Message_Exception
     * @return Zend_Mobile_Push_Message_Gcm
     */
    public function setTtl($secs)
    {
        if (!is_numeric($secs)) {
            throw new Zend_Mobile_Push_Message_Exception('$secs must be numeric');
        }
        $this->_ttl = (int) $secs;
        return $this;
    }

    /**
     * Get time to live
     *
     * @return int
     */
    public function getTtl()
    {
        return $this->_ttl;
    }

    /**
     * Validate this is a proper Gcm message
     * Does not validate size.
     *
     * @return boolean
     */
    public function validate()
    {
        if (!is_array($this->_token) || empty($this->_token)) {
            return false;
        }
        if ($this->_ttl !== 2419200 &&
            (!is_scalar($this->_id) ||
            strlen($this->_id) === 0)) {
            return false;
        }
        return true;
    }

    /**
     * To Json utility method
     * Takes the data and properly assigns it to
     * a json encoded array to match the Gcm format.
     *
     * @return string
     */
    public function toJson()
    {
        $json = array();
        if ($this->_token) {
            $json['registration_ids'] = $this->_token;
        }
        if ($this->_id) {
            $json['collapse_key'] = (string) $this->_id;
        }
        if ($this->_data) {
            $json['data'] = $this->_data;
        }
        if ($this->_delay) {
            $json['delay_while_idle'] = $this->_delay;
        }
        if ($this->_ttl !== 2419200) {
            $json['time_to_live'] = $this->_ttl;
        }
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            return json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            return json_encode($json);
        }
    }
}
