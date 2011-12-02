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
 * @version    $Id: TagSet.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Simpy_Tag
 */
#require_once 'Zend/Service/Simpy/Tag.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Simpy_TagSet implements IteratorAggregate
{
    /**
     * List of tags
     *
     * @var array of Zend_Service_Simpy_Tag objects
     */
    protected $_tags;

    /**
     * Constructor to initialize the object with data
     *
     * @param  DOMDocument $doc Parsed response from a GetTags operation
     * @return void
     */
    public function __construct(DOMDocument $doc)
    {
        $xpath = new DOMXPath($doc);
        $list = $xpath->query('//tags/tag');
        $this->_tags = array();

        for ($x = 0; $x < $list->length; $x++) {
            $this->_tags[$x] = new Zend_Service_Simpy_Tag($list->item($x));
        }
    }

    /**
     * Returns an iterator for the tag set
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_tags);
    }

    /**
     * Returns the number of tags in the set
     *
     * @return int
     */
    public function getLength()
    {
        return count($this->_tags);
    }
}
