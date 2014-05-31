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
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PaginationOutput.php 22791 2010-08-04 16:11:47Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Finding_Abstract
 */
#require_once 'Zend/Service/Ebay/Finding/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Abstract
 */
class Zend_Service_Ebay_Finding_PaginationOutput extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * The maximum number of items that can be returned in the response.
     *
     * This number is always equal to the value input for
     * paginationInput.entriesPerPage. The end of the result set has been
     * reached if the number specified for entriesPerPage is greater than the
     * number of items found on the specified pageNumber. In this case, there
     * will be fewer items returned than the number specified in entriesPerPage.
     * This can be determined by comparing the entriesPerPage value with the
     * value returned in the count attribute for the searchResult field.
     *
     * @var integer
     */
    public $entriesPerPage;

    /**
     * The subset of item data returned in the current response.
     *
     * Search results are divided into sets, or "pages," of item data. The
     * number of pages is equal to the total number of items matching the search
     * criteria divided by the value specified for entriesPerPage in the
     * request. The response for a request contains one "page" of item data.
     *
     * This returned value indicates the page number of item data returned (a
     * subset of the complete result set). If this field contains 1, the
     * response contains the first page of item data (the default). If the value
     * returned in totalEntries is less than the value for entriesPerPage,
     * pageNumber returns 1 and the response contains the entire result set.
     *
     * The value of pageNumber is normally equal to the value input for
     * paginationInput.pageNumber. However, if the number input for pageNumber
     * is greater than the total possible pages of output, eBay returns the last
     * page of item data in the result set, and the value for pageNumber is set
     * to the respective (last) page number.
     *
     * @var integer
     */
    public $pageNumber;

    /**
     * The total number of items found that match the search criteria in your
     * request.
     *
     * Depending on the input value for entriesPerPage, the response might
     * include only a portion (a page) of the entire result set. A value of "0"
     * is returned if eBay does not find any items that match the search
     * criteria.
     *
     * @var integer
     */
    public $totalEntries;

    /**
     * The total number of pages of data that could be returned by repeated
     * search requests.
     *
     * Note that if you modify the value of inputPagination.entriesPerPage in a
     * request, the value output for totalPages will change. A value of "0" is
     * returned if eBay does not find any items that match the search criteria.
     *
     * @var integer
     */
    public $totalPages;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->entriesPerPage = $this->_query(".//$ns:entriesPerPage[1]", 'integer');
        $this->pageNumber     = $this->_query(".//$ns:pageNumber[1]", 'integer');
        $this->totalEntries   = $this->_query(".//$ns:totalEntries[1]", 'integer');
        $this->totalPages     = $this->_query(".//$ns:totalPages[1]", 'integer');
    }
}
