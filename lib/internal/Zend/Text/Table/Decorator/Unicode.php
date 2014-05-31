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
 * @category  Zend
 * @package   Zend_Text_Table
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Unicode.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Text_Table_Decorator_Interface
 */
#require_once 'Zend/Text/Table/Decorator/Interface.php';

/**
 * Unicode Decorator for Zend_Text_Table
 *
 * @category  Zend
 * @package   Zend_Text_Table
 * @uses      Zend_Text_Table_Decorator_Interface
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Text_Table_Decorator_Unicode implements Zend_Text_Table_Decorator_Interface
{
    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getTopLeft()
    {
        return $this->_uniChar(0x250C);
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getTopRight()
    {
        return $this->_uniChar(0x2510);
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getBottomLeft()
    {
        return $this->_uniChar(0x2514);
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getBottomRight()
    {
        return $this->_uniChar(0x2518);
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getVertical()
    {
        return $this->_uniChar(0x2502);
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getHorizontal()
    {
        return $this->_uniChar(0x2500);
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getCross()
    {
        return $this->_uniChar(0x253C);
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getVerticalRight()
    {
        return $this->_uniChar(0x251C);
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getVerticalLeft()
    {
        return $this->_uniChar(0x2524);
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getHorizontalDown()
    {
        return $this->_uniChar(0x252C);
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getHorizontalUp()
    {
        return $this->_uniChar(0x2534);
    }

    /**
     * Convert am unicode character code to a character
     *
     * @param  integer $code
     * @return string|false
     */
    protected function _uniChar($code)
    {
        if ($code <= 0x7F) {
            $char = chr($code);
        } else if ($code <= 0x7FF) {
            $char = chr(0xC0 | $code >> 6)
                  . chr(0x80 | $code & 0x3F);
        } else if ($code <= 0xFFFF) {
            $char =  chr(0xE0 | $code >> 12)
                  . chr(0x80 | $code >> 6 & 0x3F)
                  . chr(0x80 | $code & 0x3F);
        } else if ($code <= 0x10FFFF) {
            $char =  chr(0xF0 | $code >> 18)
                  . chr(0x80 | $code >> 12 & 0x3F)
                  . chr(0x80 | $code >> 6 & 0x3F)
                  . chr(0x80 | $code & 0x3F);
        } else {
            return false;
        }

        return $char;
    }
}
