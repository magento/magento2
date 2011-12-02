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
 * @version    $Id: Container.php 22791 2010-08-04 16:11:47Z renanbr $
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
class Zend_Service_Ebay_Finding_Category_Histogram_Container extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * Response container for category histograms.
     *
     * This container is returned only when the specified category has children
     * categories.
     *
     * @var Zend_Service_Ebay_Finding_Category_Histogram_Set
     */
    public $categoryHistogram;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;


        $nodes = $this->_xPath->query(".//$ns:categoryHistogram", $this->_dom);
        if ($nodes->length > 0) {
            /**
             * @see Zend_Service_Ebay_Finding_Category_Histogram_Set
             */
            #require_once 'Zend/Service/Ebay/Finding/Category/Histogram/Set.php';
            $this->categoryHistogram = new Zend_Service_Ebay_Finding_Category_Histogram_Set($nodes);
        }
    }
}
