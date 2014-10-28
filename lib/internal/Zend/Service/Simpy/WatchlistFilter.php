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
 * @version    $Id: WatchlistFilter.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Simpy_WatchlistFilter
{
    /**
     * Name of the filter
     *
     * @var string
     */
    protected $_name;

    /**
     * Query for the filter
     *
     * @var string
     */
    protected $_query;

    /**
     * Constructor to initialize the object with data
     *
     * @param  DOMNode $node Individual <filter> node from a parsed response from
     *                       a GetWatchlists or GetWatchlist operation
     * @return void
     */
    public function __construct($node)
    {
        $map =& $node->attributes;
        $this->_name = $map->getNamedItem('name')->nodeValue;
        $this->_query = $map->getNamedItem('query')->nodeValue;
    }

    /**
     * Returns the name of the filter
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns the query for the filter
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->_query;
    }
}
