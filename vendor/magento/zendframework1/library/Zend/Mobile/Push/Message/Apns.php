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
 * Apns Message
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Mobile_Push_Message_Apns extends Zend_Mobile_Push_Message_Abstract
{
    /**
     * Badge Number
     *
     * @var int
     */
    protected $_badge;

    /**
     * Alert
     *
     * @var array
     */
    protected $_alert  = array();

    /**
     * Expiration
     *
     * @var int
     */
    protected $_expire;

    /**
     * Sound
     *
     * @var string
     */
    protected $_sound = 'default';

    /**
     * Custom Data
     *
     * @var array
     */
    protected $_custom = array();

    /**
     * Get Alert
     *
     * @return array
     */
    public function getAlert()
    {
        return $this->_alert;
    }

    /**
     * Set Alert
     *
     * @param  string      $text
     * @param  string|null $actionLocKey
     * @param  string|null $locKey
     * @param  array|null  $locArgs
     * @param  string|null $launchImage
     * @throws Zend_Mobile_Push_Message_Exception
     * @return Zend_Mobile_Push_Message_Apns
     */
    public function setAlert($text, $actionLocKey=null, $locKey=null, $locArgs=null, $launchImage=null)
    {
        if ($text !== null && !is_string($text)) {
            throw new Zend_Mobile_Push_Message_Exception('$text must be a string');
        }

        if ($actionLocKey !== null && !is_string($actionLocKey)) {
            throw new Zend_Mobile_Push_Message_Exception('$actionLocKey must be a string');
        }

        if ($locKey !== null && !is_string($locKey)) {
            throw new Zend_Mobile_Push_Message_Exception('$locKey must be a string');
        }

        if ($locArgs !== null) {
            if (!is_array($locArgs)) {
                throw new Zend_Mobile_Push_Message_Exception('$locArgs must be an array of strings');
            } else {
                foreach ($locArgs as $str) {
                    if (!is_string($str)) {
                        throw new Zend_Mobile_Push_Message_Exception('$locArgs contains an item that is not a string');
                    }
                }
            }
        }

        if (null !== $launchImage && !is_string($launchImage)) {
            throw new Zend_Mobile_Push_Message_Exception('$launchImage must be a string');
        }

        $this->_alert = array(
            'body'           => $text,
            'action-loc-key' => $actionLocKey,
            'loc-key'        => $locKey,
            'loc-args'       => $locArgs,
            'launch-image'   => $launchImage,
        );
        return $this;
    }

    /**
     * Get Badge
     *
     * @return int
     */
    public function getBadge()
    {
        return $this->_badge;
    }

    /**
     * Set Badge
     *
     * @param int $badge
     * @return Zend_Mobile_Push_Message_Apns
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setBadge($badge)
    {
        if (!is_null($badge) && !is_numeric($badge)) {
            throw new Zend_Mobile_Push_Message_Exception('$badge must be an integer');
        }
        if (!is_null($badge) && $badge < 0) {
            throw new Zend_Mobile_Push_Message_Exception('$badge must be greater or equal to 0');
        }
        $this->_badge = $badge;
    }

    /**
     * Get Expire
     *
     * @return int
     */
    public function getExpire()
    {
        return $this->_expire;
    }

    /**
     * Set Expire
     *
     * @param int $expire
     * @return Zend_Mobile_Push_Message_Apns
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setExpire($expire)
    {
        if (!is_numeric($expire)) {
            throw new Zend_Mobile_Push_Message_Exception('$expire must be an integer');
        }
        $this->_expire = (int) $expire;
        return $this;
    }

    /**
     * Get Sound
     *
     * @return string
     */
    public function getSound()
    {
        return $this->_sound;
    }

    /**
     * Set Sound
     *
     * @param string $sound
     * @return Zend_Mobile_Push_Message_Apns
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setSound($sound)
    {
        if (!is_string($sound)) {
            throw new Zend_Mobile_Push_Message_Exception('$sound must be a string');
        }
        $this->_sound = $sound;
        return $this;
    }

    /**
     * Add Custom Data
     *
     * @param string $key
     * @param mixed $value
     * @return Zend_Mobile_Push_Message_Apns
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function addCustomData($key, $value)
    {
        if (!is_string($key)) {
            throw new Zend_Mobile_Push_Message_Exception('$key is not a string');
        }
        if ($key == 'aps') {
            throw new Zend_Mobile_Push_Message_Exception('$key must not be aps as it is reserved by apple');
        }
        $this->_custom[$key] = $value;
    }

    /**
     * Clear Custom Data
     *
     * @return throw new Zend_Mobile_Push_Message_Apns
     */
    public function clearCustomData()
    {
        $this->_custom = array();
        return $this;
    }

    /**
     * Set Custom Data
     *
     * @param  array $array
     * @throws Zend_Mobile_Push_Message_Exception
     * @return Zend_Mobile_Push_Message_Apns
     */
    public function setCustomData($array)
    {
        $this->_custom = array();
        foreach ($array as $k => $v) {
            $this->addCustomData($k, $v);
        }
        return $this;
    }

    /**
     * Get Custom Data
     *
     * @return array
     */
    public function getCustomData()
    {
        return $this->_custom;
    }

    /**
     * Validate this is a proper Apns message
     *
     * @return boolean
     */
    public function validate()
    {
        if (!is_string($this->_token) || strlen($this->_token) === 0) {
            return false;
        }
        if (null != $this->_id && !is_numeric($this->_id)) {
            return false;
        }
        return true;
    }
}
