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
 * @version    $Id: DailyCountsResultSet.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Date
 */
#require_once 'Zend/Date.php';

/**
 * @see Zend_Service_Technorati_ResultSet
 */
#require_once 'Zend/Service/Technorati/ResultSet.php';

/**
 * @see Zend_Service_Technorati_Utils
 */
#require_once 'Zend/Service/Technorati/Utils.php';


/**
 * Represents a Technorati Tag query result set.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_DailyCountsResultSet extends Zend_Service_Technorati_ResultSet
{
    /**
     * Technorati search URL for given query.
     *
     * @var     Zend_Uri_Http
     * @access  protected
     */
    protected $_searchUrl;

    /**
     * Number of days for which counts provided.
     *
     * @var     Zend_Service_Technorati_Weblog
     * @access  protected
     */
    protected $_days;

    /**
     * Parses the search response and retrieve the results for iteration.
     *
     * @param   DomDocument $dom    the ReST fragment for this object
     * @param   array $options      query options as associative array
     */
    public function __construct(DomDocument $dom, $options = array())
    {
        parent::__construct($dom, $options);

        // default locale prevent Zend_Date to fail
        // when script is executed via shell
        // Zend_Locale::setDefault('en');

        $result = $this->_xpath->query('/tapi/document/result/days/text()');
        if ($result->length == 1) $this->_days = (int) $result->item(0)->data;

        $result = $this->_xpath->query('/tapi/document/result/searchurl/text()');
        if ($result->length == 1) {
            $this->_searchUrl = Zend_Service_Technorati_Utils::normalizeUriHttp($result->item(0)->data);
        }

        $this->_totalResultsReturned  = (int) $this->_xpath->evaluate("count(/tapi/document/items/item)");
        $this->_totalResultsAvailable = (int) $this->getDays();
    }


    /**
     * Returns the search URL for given query.
     *
     * @return  Zend_Uri_Http
     */
    public function getSearchUrl() {
        return $this->_searchUrl;
    }

    /**
     * Returns the number of days for which counts provided.
     *
     * @return  int
     */
    public function getDays() {
        return $this->_days;
    }

    /**
     * Implements Zend_Service_Technorati_ResultSet::current().
     *
     * @return Zend_Service_Technorati_DailyCountsResult current result
     */
    public function current()
    {
        /**
         * @see Zend_Service_Technorati_DailyCountsResult
         */
        #require_once 'Zend/Service/Technorati/DailyCountsResult.php';
        return new Zend_Service_Technorati_DailyCountsResult($this->_results->item($this->_currentIndex));
    }
}
