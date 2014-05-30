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
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TagResultSet.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Technorati_ResultSet
 */
#require_once 'Zend/Service/Technorati/ResultSet.php';


/**
 * Represents a Technorati Tag query result set.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_TagResultSet extends Zend_Service_Technorati_ResultSet
{
    /**
     * Number of posts that match the tag.
     *
     * @var     int
     * @access  protected
     */
    protected $_postsMatched;

    /**
     * Number of blogs that match the tag.
     *
     * @var     int
     * @access  protected
     */
    protected $_blogsMatched;

    /**
     * Parses the search response and retrieve the results for iteration.
     *
     * @param   DomDocument $dom    the ReST fragment for this object
     * @param   array $options      query options as associative array
     */
    public function __construct(DomDocument $dom, $options = array())
    {
        parent::__construct($dom, $options);

        $result = $this->_xpath->query('/tapi/document/result/postsmatched/text()');
        if ($result->length == 1) $this->_postsMatched = (int) $result->item(0)->data;

        $result = $this->_xpath->query('/tapi/document/result/blogsmatched/text()');
        if ($result->length == 1) $this->_blogsMatched = (int) $result->item(0)->data;

        $this->_totalResultsReturned  = (int) $this->_xpath->evaluate("count(/tapi/document/item)");
        /** @todo Validate the following assertion */
        $this->_totalResultsAvailable = (int) $this->getPostsMatched();
    }


    /**
     * Returns the number of posts that match the tag.
     *
     * @return  int
     */
    public function getPostsMatched() {
        return $this->_postsMatched;
    }

    /**
     * Returns the number of blogs that match the tag.
     *
     * @return  int
     */
    public function getBlogsMatched() {
        return $this->_blogsMatched;
    }

    /**
     * Implements Zend_Service_Technorati_ResultSet::current().
     *
     * @return Zend_Service_Technorati_TagResult current result
     */
    public function current()
    {
        /**
         * @see Zend_Service_Technorati_TagResult
         */
        #require_once 'Zend/Service/Technorati/TagResult.php';
        return new Zend_Service_Technorati_TagResult($this->_results->item($this->_currentIndex));
    }
}
