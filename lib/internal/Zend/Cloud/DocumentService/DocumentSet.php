<?php
/**
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
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Class encapsulating a set of documents
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_DocumentSet implements Countable, IteratorAggregate
{
    /** @var int */
    protected $_documentCount;

    /** @var ArrayIterator */
    protected $_documents;

    /**
     * Constructor
     *
     * @param  array $documents
     * @return void
     */
    public function __construct(array $documents)
    {
        $this->_documentCount = count($documents);
        $this->_documents     = new ArrayIterator($documents);
    }

    /**
     * Countable: number of documents in set
     * 
     * @return int
     */
    public function count()
    {
        return $this->_documentCount;
    }

    /**
     * IteratorAggregate: retrieve iterator
     * 
     * @return Traversable
     */
    public function getIterator()
    {
        return $this->_documents;
    }
}
