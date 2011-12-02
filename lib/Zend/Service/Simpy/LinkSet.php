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
 * @version    $Id: LinkSet.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Simpy_Link
 */
#require_once 'Zend/Service/Simpy/Link.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Simpy_LinkSet implements IteratorAggregate
{
    /**
     * List of links
     *
     * @var array of Zend_Service_Simpy_Link objects
     */
    protected $_links;

    /**
     * Constructor to initialize the object with data
     *
     * @param  DOMDocument $doc Parsed response from a GetLinks operation
     * @return void
     */
    public function __construct(DOMDocument $doc)
    {
        $xpath = new DOMXPath($doc);
        $list = $xpath->query('//links/link');
        $this->_links = array();

        for ($x = 0; $x < $list->length; $x++) {
            $this->_links[$x] = new Zend_Service_Simpy_Link($list->item($x));
        }
    }

    /**
     * Returns an iterator for the link set
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_links);
    }

    /**
     * Returns the number of links in the set
     *
     * @return int
     */
    public function getLength()
    {
        return count($this->_links);
    }
}
