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
 * @subpackage Flickr
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Service_Flickr_Result
 */
#require_once 'Zend/Service/Flickr/Result.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Flickr
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Flickr_ResultSet implements SeekableIterator
{
    /**
     * Total number of available results
     *
     * @var int
     */
    public $totalResultsAvailable;

    /**
     * Number of results in this result set
     *
     * @var int
     */
    public $totalResultsReturned;

    /**
     * The offset of this result set in the total set of available results
     *
     * @var int
     */
    public $firstResultPosition;

    /**
     * Results storage
     *
     * @var DOMNodeList
     */
    protected $_results = null;

    /**
     * Reference to Zend_Service_Flickr object with which the request was made
     *
     * @var Zend_Service_Flickr
     */
    private $_flickr;

    /**
     * Current index for the Iterator
     *
     * @var int
     */
    private $_currentIndex = 0;

    /**
     * Parse the Flickr Result Set
     *
     * @param  DOMDocument         $dom
     * @param  Zend_Service_Flickr $flickr
     * @return void
     */
    public function __construct(DOMDocument $dom, Zend_Service_Flickr $flickr)
    {
        $this->_flickr = $flickr;

        $xpath = new DOMXPath($dom);

        $photos = $xpath->query('//photos')->item(0);

        $page    = $photos->getAttribute('page');
        $pages   = $photos->getAttribute('pages');
        $perPage = $photos->getAttribute('perpage');
        $total   = $photos->getAttribute('total');

        $this->totalResultsReturned  = ($page == $pages || $pages == 0) ? ($total - ($page - 1) * $perPage) : (int) $perPage;
        $this->firstResultPosition   = ($page - 1) * $perPage + 1;
        $this->totalResultsAvailable = (int) $total;

        if ($total > 0) {
            $this->_results = $xpath->query('//photo');
        }
    }

    /**
     * Total Number of results returned
     *
     * @return int Total number of results returned
     */
    public function totalResults()
    {
        return $this->totalResultsReturned;
    }

    /**
     * Implements SeekableIterator::current()
     *
     * @return Zend_Service_Flickr_Result
     */
    public function current()
    {
        return new Zend_Service_Flickr_Result($this->_results->item($this->_currentIndex), $this->_flickr);
    }

    /**
     * Implements SeekableIterator::key()
     *
     * @return int
     */
    public function key()
    {
        return $this->_currentIndex;
    }

    /**
     * Implements SeekableIterator::next()
     *
     * @return void
     */
    public function next()
    {
        $this->_currentIndex += 1;
    }

    /**
     * Implements SeekableIterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
        $this->_currentIndex = 0;
    }

    /**
     * Implements SeekableIterator::seek()
     *
     * @param  int $index
     * @throws OutOfBoundsException
     * @return void
     */
    public function seek($index)
    {
        $indexInt = (int) $index;
        if ($indexInt >= 0 && (null === $this->_results || $indexInt < $this->_results->length)) {
            $this->_currentIndex = $indexInt;
        } else {
            throw new OutOfBoundsException("Illegal index '$index'");
        }
    }

    /**
     * Implements SeekableIterator::valid()
     *
     * @return boolean
     */
    public function valid()
    {
        return null !== $this->_results && $this->_currentIndex < $this->_results->length;
    }
}

