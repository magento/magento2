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
 * @version   $Id: Interface.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Interface for Zend_Text_Table decorators
 *
 * @category  Zend
 * @package   Zend_Text_Table
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Text_Table_Decorator_Interface
{
    /**
     * Get a single character for the top left corner
     *
     * @return string
     */
    public function getTopLeft();

    /**
     * Get a single character for the top right corner
     *
     * @return string
     */
    public function getTopRight();

    /**
     * Get a single character for the bottom left corner
     *
     * @return string
     */
    public function getBottomLeft();

    /**
     * Get a single character for the bottom right corner
     *
     * @return string
     */
    public function getBottomRight();

    /**
     * Get a single character for a vertical line
     *
     * @return string
     */
    public function getVertical();

    /**
     * Get a single character for a horizontal line
     *
     * @return string
     */
    public function getHorizontal();

    /**
     * Get a single character for a crossing line
     *
     * @return string
     */
    public function getCross();

    /**
     * Get a single character for a vertical divider right
     *
     * @return string
     */
    public function getVerticalRight();

    /**
     * Get a single character for a vertical divider left
     *
     * @return string
     */
    public function getVerticalLeft();

    /**
     * Get a single character for a horizontal divider down
     *
     * @return string
     */
    public function getHorizontalDown();

    /**
     * Get a single character for a horizontal divider up
     *
     * @return string
     */
    public function getHorizontalUp();
}
