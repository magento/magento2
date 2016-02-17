<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Text\Table\Decorator;

use Zend\Text\Table\Decorator\DecoratorInterface as Decorator;

/**
 * Unicode Decorator for Zend\Text\Table
 */
class Unicode implements Decorator
{
    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getTopLeft()
    {
        return $this->_uniChar(0x250C);
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getTopRight()
    {
        return $this->_uniChar(0x2510);
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getBottomLeft()
    {
        return $this->_uniChar(0x2514);
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getBottomRight()
    {
        return $this->_uniChar(0x2518);
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVertical()
    {
        return $this->_uniChar(0x2502);
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontal()
    {
        return $this->_uniChar(0x2500);
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getCross()
    {
        return $this->_uniChar(0x253C);
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVerticalRight()
    {
        return $this->_uniChar(0x251C);
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVerticalLeft()
    {
        return $this->_uniChar(0x2524);
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontalDown()
    {
        return $this->_uniChar(0x252C);
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
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
     * @param  int $code
     * @return string|false
     */
    protected function _uniChar($code)
    {
        if ($code <= 0x7F) {
            $char = chr($code);
        } elseif ($code <= 0x7FF) {
            $char = chr(0xC0 | $code >> 6)
                  . chr(0x80 | $code & 0x3F);
        } elseif ($code <= 0xFFFF) {
            $char =  chr(0xE0 | $code >> 12)
                  . chr(0x80 | $code >> 6 & 0x3F)
                  . chr(0x80 | $code & 0x3F);
        } elseif ($code <= 0x10FFFF) {
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
