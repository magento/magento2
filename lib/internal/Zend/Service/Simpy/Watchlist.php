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
 * @version    $Id: Watchlist.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Simpy_WatchlistFilterSet
 */
#require_once 'Zend/Service/Simpy/WatchlistFilterSet.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Simpy_Watchlist
{
    /**
     * Identifier for the watchlist
     *
     * @var int
     */
    protected $_id;

    /**
     * Name of the watchlist
     *
     * @var string
     */
    protected $_name;

    /**
     * Description of the watchlist
     *
     * @var string
     */
    protected $_description;

    /**
     * Timestamp for when the watchlist was added
     *
     * @var string
     */
    protected $_addDate;

    /**
     * Number of new links in the watchlist
     *
     * @var int
     */
    protected $_newLinks;

    /**
     * List of usernames for users included in the watchlist
     *
     * @var array
     */
    protected $_users;

    /**
     * List of filters included in the watchlist
     *
     * @var Zend_Service_Simpy_WatchlistFilterSet
     */
    protected $_filters;

    /**
     * Constructor to initialize the object with data
     *
     * @param  DOMNode $node Individual <watchlist> node from a parsed
     *                       response from a GetWatchlists or GetWatchlist
     *                       operation
     * @return void
     */
    public function __construct($node)
    {
        $map =& $node->attributes;

        $this->_id = $map->getNamedItem('id')->nodeValue;
        $this->_name = $map->getNamedItem('name')->nodeValue;
        $this->_description = $map->getNamedItem('description')->nodeValue;
        $this->_addDate = $map->getNamedItem('addDate')->nodeValue;
        $this->_newLinks = $map->getNamedItem('newLinks')->nodeValue;

        $this->_users = array();
        $this->_filters = new Zend_Service_Simpy_WatchlistFilterSet();

        $childNode = $node->firstChild;
        while ($childNode !== null) {
            if ($childNode->nodeName == 'user') {
                $this->_users[] = $childNode->attributes->getNamedItem('username')->nodeValue;
            } elseif ($childNode->nodeName == 'filter') {
                $filter = new Zend_Service_Simpy_WatchlistFilter($childNode);
                $this->_filters->add($filter);
            }
            $childNode = $childNode->nextSibling;
        }
    }

    /**
     * Returns the identifier for the watchlist
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the name of the watchlist
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns the description of the watchlist
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Returns a timestamp for when the watchlist was added
     *
     * @return string
     */
    public function getAddDate()
    {
        return $this->_addDate;
    }

    /**
     * Returns the number of new links in the watchlist
     *
     * @return int
     */
    public function getNewLinks()
    {
        return $this->_newLinks;
    }

    /**
     * Returns a list of usernames for users included in the watchlist
     *
     * @return array
     */
    public function getUsers()
    {
        return $this->_users;
    }

    /**
     * Returns a list of filters included in the watchlist
     *
     * @return Zend_Service_Simpy_WatchlistFilterSet
     */
    public function getFilters()
    {
        return $this->_filters;
    }
}
