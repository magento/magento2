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
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_QueryHit
{
    /**
     * Object handle of the index
     * @var Zend_Search_Lucene_Interface
     */
    protected $_index = null;

    /**
     * Object handle of the document associated with this hit
     * @var Zend_Search_Lucene_Document
     */
    protected $_document = null;

    /**
     * Number of the document in the index
     * @var integer
     */
    public $id;

    /**
     * Score of the hit
     * @var float
     */
    public $score;


    /**
     * Constructor - pass object handle of Zend_Search_Lucene_Interface index that produced
     * the hit so the document can be retrieved easily from the hit.
     *
     * @param Zend_Search_Lucene_Interface $index
     */

    public function __construct(Zend_Search_Lucene_Interface $index)
    {
        #require_once 'Zend/Search/Lucene/Proxy.php';
        $this->_index = new Zend_Search_Lucene_Proxy($index);
    }


    /**
     * Convenience function for getting fields from the document
     * associated with this hit.
     *
     * @param string $offset
     * @return string
     */
    public function __get($offset)
    {
        return $this->getDocument()->getFieldValue($offset);
    }


    /**
     * Return the document object for this hit
     *
     * @return Zend_Search_Lucene_Document
     */
    public function getDocument()
    {
        if (!$this->_document instanceof Zend_Search_Lucene_Document) {
            $this->_document = $this->_index->getDocument($this->id);
        }

        return $this->_document;
    }


    /**
     * Return the index object for this hit
     *
     * @return Zend_Search_Lucene_Interface
     */
    public function getIndex()
    {
        return $this->_index;
    }
}

