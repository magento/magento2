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
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Tag.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Simpy_Tag
{
    /**
     * Name of the tag
     *
     * @var string
     */
    protected $_tag;

    /**
     * Number of links with the tag
     *
     * @var int
     */
    protected $_count;

    /**
     * Constructor to initialize the object with data
     *
     * @param  DOMNode $node Individual <tag> node from a parsed response from
     *                       a GetTags operation
     * @return void
     */
    public function __construct($node)
    {
        $map =& $node->attributes;
        $this->_tag = $map->getNamedItem('name')->nodeValue;
        $this->_count = $map->getNamedItem('count')->nodeValue;
    }

    /**
     * Returns the name of the tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->_tag;
    }

    /**
     * Returns the number of links with the tag
     *
     * @return int
     */
    public function getCount()
    {
        return $this->_count;
    }
}
