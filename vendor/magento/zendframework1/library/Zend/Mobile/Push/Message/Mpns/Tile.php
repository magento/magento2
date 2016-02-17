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
 */

/** Zend_Mobile_Push_Message_Mpns **/
#require_once 'Zend/Mobile/Push/Message/Mpns.php';

/**
 * Mpns Tile Message
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mobile_Push_Message_Mpns_Tile extends Zend_Mobile_Push_Message_Mpns
{
    /**
     * Mpns delays
     *
     * @var int
     */
    const DELAY_IMMEDIATE = 1;
    const DELAY_450S = 11;
    const DELAY_900S = 21;

    /**
     * Background Image
     *
     * @var string
     */
    protected $_backgroundImage;

    /**
     * Count
     *
     * @var int
     */
    protected $_count = 0;

    /**
     * Title
     *
     * @var string
     */
    protected $_title;

    /**
     * Back Background Image
     *
     * @var string
     */
    protected $_backBackgroundImage;

    /**
     * Back Title
     *
     * @var string
     */
    protected $_backTitle;

    /**
     * Back Content
     *
     * @var string
     */
    protected $_backContent;

    /**
     * Tile ID
     *
     * @var string
     */
    protected $_tileId;

    /**
     * Get Background Image
     *
     * @return string
     */
    public function getBackgroundImage()
    {
        return $this->_backgroundImage;
    }

    /**
     * Set Background Image
     *
     * @param string $bgImg
     * @return Zend_Mobile_Push_Message_Mpns_Tile
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setBackgroundImage($bgImg)
    {
        if (!is_string($bgImg)) {
            throw new Zend_Mobile_Push_Message_Exception('$bgImg must be a string');
        }
        $this->_backgroundImage = $bgImg;
        return $this;
    }

    /**
     * Get Count
     *
     * @return int
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * Set Count
     *
     * @param int $count
     * @return Zend_Mobile_Push_Message_Mpns_Tile
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setCount($count)
    {
        if (!is_numeric($count)) {
            throw new Zend_Mobile_Push_Message_Exception('$count is not numeric');
        }
        $this->_count = (int) $count;
        return $this;
    }

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set Title
     *
     * @param string $title
     * @return Zend_Mobile_Push_Message_Mpns_Tile
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setTitle($title)
    {
        if (!is_string($title)) {
            throw new Zend_Mobile_Push_Message_Exception('$title must be a string');
        }
        $this->_title = $title;
        return $this;
    }

    /**
     * Get Back Background Image
     *
     * @return string
     */
    public function getBackBackgroundImage()
    {
        return $this->_backBackgroundImage;
    }

    /**
     * Set Back Background Image
     *
     * @param string $bgImg
     * @return Zend_Mobile_Push_Message_Mpns_Tile
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setBackBackgroundImage($bgImg)
    {
        if (!is_string($bgImg)) {
            throw new Zend_Mobile_Push_Message_Exception('$bgImg must be a string');
        }
        $this->_backBackgroundImage = $bgImg;
        return $this;
    }

    /**
     * Get Back Title
     *
     * @return string
     */
    public function getBackTitle()
    {
        return $this->_backTitle;
    }

    /**
     * Set Back Title
     *
     * @param string $title
     * @return Zend_Mobile_Push_Message_Mpns_Tile
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setBackTitle($title)
    {
        if (!is_string($title)) {
            throw new Zend_Mobile_Push_Message_Exception('$title must be a string');
        }
        $this->_backTitle = $title;
        return $this;
    }

    /**
     * Get Back Content
     *
     * @return string
     */
    public function getBackContent()
    {
        return $this->_backContent;
    }

    /**
     * Set Back Content
     *
     * @param string $content
     * @return Zend_Mobile_Push_Message_Mpns_Tile
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setBackContent($content)
    {
        if (!is_string($content)) {
            throw new Zend_Mobile_Push_Message_Exception('$content must be a string');
        }
        $this->_backContent = $content;
    }

    /**
     * Get Tile Id
     *
     * @return string
     */
    public function getTileId()
    {
        return $this->_tileId;
    }

    /**
     * Set Tile Id
     *
     * @param string $tileId
     * @return Zend_Mobile_Push_Message_Mpns_Tile
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setTileId($tileId)
    {
        if (!is_string($tileId)) {
            throw new Zend_Mobile_Push_Message_Exception('$tileId is not a string');
        }
        $this->_tileId = $tileId;
        return $this;
    }

    /**
     * Get Delay
     *
     * @return int
     */
    public function getDelay()
    {
        if (!$this->_delay) {
            return self::DELAY_IMMEDIATE;
        }
        return $this->_delay;
    }

    /**
     * Set Delay
     *
     * @param int $delay
     * @return Zend_Mobile_Push_Message_Mpns_Tile
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setDelay($delay)
    {
        if (!in_array($delay, array(
            self::DELAY_IMMEDIATE,
            self::DELAY_450S,
            self::DELAY_900S
        ))) {
            throw new Zend_Mobile_Push_Message_Exception('$delay must be one of the DELAY_* constants');
        }
        $this->_delay = $delay;
        return $this;
    }

    /**
     * Get Notification Type
     *
     * @return string
     */
    public static function getNotificationType()
    {
        return 'token';
    }

    /**
     * Get XML Payload
     *
     * @return string
     */
    public function getXmlPayload()
    {
        $ret = '<?xml version="1.0" encoding="utf-8"?>'
            . '<wp:Notification xmlns:wp="WPNotification">'
            . '<wp:Tile' . (($this->_tileId) ? ' Id="' . htmlspecialchars($this->_tileId) . '"' : '') . '>'
            . '<wp:BackgroundImage>' . htmlspecialchars($this->_backgroundImage) . '</wp:BackgroundImage>'
            . '<wp:Count>' . (int) $this->_count . '</wp:Count>'
            . '<wp:Title>' . htmlspecialchars($this->_title) . '</wp:Title>';

        if ($this->_backBackgroundImage) {
            $ret .= '<wp:BackBackgroundImage>' . htmlspecialchars($this->_backBackgroundImage) . '</wp:BackBackgroundImage>';
        }
        if ($this->_backTitle) {
            $ret .= '<wp:BackTitle>' . htmlspecialchars($this->_backTitle) . '</wp:BackTitle>';
        }
        if ($this->_backContent) {
            $ret .= '<wp:BackContent>' . htmlspecialchars($this->_backContent) . '</wp:BackContent>';
        }

        $ret .= '</wp:Tile>'
            . '</wp:Notification>';
        return $ret;
    }

    /**
     * Validate proper mpns message
     *
     * @return boolean
     */
    public function validate()
    {
        if (!isset($this->_token) || strlen($this->_token) === 0) {
            return false;
        }
        if (empty($this->_backgroundImage)) {
            return false;
        }
        if (empty($this->_title)) {
            return false;
        }
        return parent::validate();
    }
}
