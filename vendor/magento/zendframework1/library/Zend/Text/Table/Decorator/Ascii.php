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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id$
 */

/**
 * @see Zend_Text_Table_Decorator_Interface
 */
#require_once 'Zend/Text/Table/Decorator/Interface.php';

/**
 * ASCII Decorator for Zend_Text_Table
 *
 * @category  Zend
 * @package   Zend_Text_Table
 * @uses      Zend_Text_Table_Decorator_Interface
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Text_Table_Decorator_Ascii implements Zend_Text_Table_Decorator_Interface
{
    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getTopLeft()
    {
        return '+';
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getTopRight()
    {
        return '+';
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getBottomLeft()
    {
        return '+';
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getBottomRight()
    {
        return '+';
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getVertical()
    {
        return '|';
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getHorizontal()
    {
        return '-';
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getCross()
    {
        return '+';
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getVerticalRight()
    {
        return '+';
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getVerticalLeft()
    {
        return '+';
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getHorizontalDown()
    {
        return '+';
    }

    /**
     * Defined by Zend_Text_Table_Decorator_Interface
     *
     * @return string
     */
    public function getHorizontalUp()
    {
        return '+';
    }
}
