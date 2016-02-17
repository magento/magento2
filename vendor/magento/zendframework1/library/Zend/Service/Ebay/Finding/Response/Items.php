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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Items.php 22804 2010-08-08 05:08:05Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Finding_Response_Histograms
 */
#require_once 'Zend/Service/Ebay/Finding/Response/Histograms.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Response_Histograms
 */
class Zend_Service_Ebay_Finding_Response_Items extends Zend_Service_Ebay_Finding_Response_Histograms
{
    /**
     * @link http://developer.ebay.com/DevZone/finding/CallRef/types/PaginationInput.html
     */
    const PAGE_MAX_DEFAULT  = 100;
    const PAGE_MAX_INFINITY = 0;

    /**
     * Indicates the pagination of the result set.
     *
     * Child elements indicate the page number that is returned, the maximum
     * number of item listings to return per page, total number of pages that
     * can be returned, and the total number of listings that match the search
     * criteria.
     *
     * @var Zend_Service_Ebay_Finding_PaginationOutput
     */
    public $paginationOutput;

    /**
     * Container for the item listings that matched the search criteria.
     *
     * The data for each item is returned in individual containers, if any
     * matches were found.
     *
     * @var Zend_Service_Ebay_Finding_Search_Result
     */
    public $searchResult;

    /**
     * @var Zend_Service_Ebay_Finding_Response_Items[]
     */
    protected static $_pageCache = array();

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->_attributes['searchResult'] = array(
            'count' => $this->_query(".//$ns:searchResult[1]/@count[1]", 'string')
        );

        $node = $this->_xPath->query(".//$ns:searchResult[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_Search_Result
             */
            #require_once 'Zend/Service/Ebay/Finding/Search/Result.php';
            $this->searchResult = new Zend_Service_Ebay_Finding_Search_Result($node);
        }

        $node = $this->_xPath->query(".//$ns:paginationOutput[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_PaginationOutput
             */
            #require_once 'Zend/Service/Ebay/Finding/PaginationOutput.php';
            $this->paginationOutput = new Zend_Service_Ebay_Finding_PaginationOutput($node);
        }
    }

    /**
     * @param  Zend_Service_Ebay_Finding $proxy
     * @param  integer                   $number
     * @throws Zend_Service_Ebay_Finding_Exception When $number is invalid
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function page(Zend_Service_Ebay_Finding $proxy, $number)
    {
        // check page number
        if ($number < 1 || $number > $this->paginationOutput->totalPages) {
            /**
             * @see Zend_Service_Ebay_Finding_Exception
             */
            #require_once 'Zend/Service/Ebay/Finding/Exception.php';
            throw new Zend_Service_Ebay_Finding_Exception(
                "Page number '{$number}' is out of range.");
        }

        // prepare arguments
        $arguments = array();
        switch ($this->_operation) {
            case 'findItemsAdvanced':
                $arguments[] = $this->getOption('keywords');
                $arguments[] = $this->getOption('descriptionSearch');
                $arguments[] = $this->getOption('categoryId');
                break;

            case 'findItemsByCategory':
                $arguments[] = $this->getOption('categoryId');
                break;

            case 'findItemsByKeywords':
                $arguments[] = $this->getOption('keywords');
                break;

            case 'findItemsByProduct':
                $productId = $this->getOption('productId');
                if (!is_array($productId)) {
                    $productId = array('' => $productId);
                }
                $arguments[] = array_key_exists('', $productId)
                             ? $productId['']
                             : null;
                $arguments[] = array_key_exists('type', $productId)
                             ? $productId['type']
                             : null;
                break;

            case 'findItemsIneBayStores':
                $arguments[] = $this->getOption('storeName');
                break;

            default:
                /**
                 * @see Zend_Service_Ebay_Finding_Exception
                 */
                #require_once 'Zend/Service/Ebay/Finding/Exception.php';
                throw new Zend_Service_Ebay_Finding_Exception(
                    "Invalid operation '{$this->_operation}'.");
        }

        // prepare options
        // remove every pagination entry from current option list
        $options = $this->_options;
        foreach (array_keys($options) as $optionName) {
            if (substr($optionName, 0, 15) == 'paginationInput') {
                unset($options[$optionName]);
            }
        }

        // set new pagination values
        // see more at http://developer.ebay.com/DevZone/finding/CallRef/types/PaginationInput.html
        $entriesPerPage             = $this->paginationOutput->entriesPerPage;
        $options['paginationInput'] = array('entriesPerPage' => $entriesPerPage,
                                            'pageNumber'     => $number);

        // add current options as last argument
        ksort($options);
        $arguments[] = $options;

        // verify cache
        $id = serialize($arguments);
        if (!array_key_exists($id, self::$_pageCache)) {
            if ($number == $this->paginationOutput->pageNumber) {
                // add itself to cache
                $new = $this;
            } else {
                // request new page
                $callback = array($proxy, $this->_operation);
                $new      = call_user_func_array($callback, $arguments);
            }
            self::$_pageCache[$id] = $new;
        }

        return self::$_pageCache[$id];
    }

    /**
     * @param  Zend_Service_Ebay_Finding $proxy
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function pageFirst(Zend_Service_Ebay_Finding $proxy)
    {
        return $this->page($proxy, 1);
    }

    /**
     * @param  Zend_Service_Ebay_Finding $proxy
     * @param  integer                   $max
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function pageLast(Zend_Service_Ebay_Finding $proxy, $max = self::PAGE_MAX_DEFAULT)
    {
        $last = $this->paginationOutput->totalPages;
        if ($max > 0 && $last > $max) {
            $last = $max;
        }
        return $this->page($proxy, $last);
    }

    /**
     * @param  Zend_Service_Ebay_Finding $proxy
     * @param  integer                   $max
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function pageNext(Zend_Service_Ebay_Finding $proxy, $max = self::PAGE_MAX_DEFAULT)
    {
        $next = $this->paginationOutput->pageNumber + 1;
        $last = $this->paginationOutput->totalPages;
        if (($max > 0 && $next > $max) || $next > $last) {
            return null;
        }
        return $this->page($proxy, $next);
    }

    /**
     * @param  Zend_Service_Ebay_Finding $proxy
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function pagePrevious(Zend_Service_Ebay_Finding $proxy)
    {
        $previous = $this->paginationOutput->pageNumber - 1;
        if ($previous < 1) {
            return null;
        }
        return $this->page($proxy, $previous);
    }
}
