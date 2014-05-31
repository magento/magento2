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
 * @version    $Id: Histogram.php 22791 2010-08-04 16:11:47Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Finding_Abstract
 */
#require_once 'Zend/Service/Ebay/Finding/Category.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Category
 */
class Zend_Service_Ebay_Finding_Category_Histogram extends Zend_Service_Ebay_Finding_Category
{
    /**
     * Container for histogram information pertaining to a child of the category
     * specified in the request.
     *
     * Histograms return data on up to 10 children. Histograms are only a single
     * level deep. That is, a given category histogram contains only immediate
     * children.
     *
     * @var Zend_Service_Ebay_Finding_Category_Histogram_Set
     */
    public $childCategoryHistogram;

    /**
     * The total number of items in the associated category that match the
     * search criteria.
     *
     * @var integer
     */
    public $count;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->count = $this->_query(".//$ns:count[1]", 'integer');

        $nodes = $this->_xPath->query(".//$ns:childCategoryHistogram", $this->_dom);
        if ($nodes->length > 0) {
            /**
             * @see Zend_Service_Ebay_Finding_Category_Histogram_Set
             */
            #require_once 'Zend/Service/Ebay/Finding/Category/Histogram/Set.php';
            $this->childCategoryHistogram = new Zend_Service_Ebay_Finding_Category_Histogram_Set($nodes);
        }
    }
}
