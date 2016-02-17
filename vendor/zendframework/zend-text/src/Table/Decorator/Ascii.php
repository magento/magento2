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
 * ASCII Decorator for Zend\Text\Table
 */
class Ascii implements Decorator
{
    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getTopLeft()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getTopRight()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getBottomLeft()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getBottomRight()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVertical()
    {
        return '|';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontal()
    {
        return '-';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getCross()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVerticalRight()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVerticalLeft()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontalDown()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontalUp()
    {
        return '+';
    }
}
