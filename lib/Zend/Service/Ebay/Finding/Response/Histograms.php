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
 * @version    $Id: Histograms.php 22804 2010-08-08 05:08:05Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Finding_Response_Abstract
 */
#require_once 'Zend/Service/Ebay/Finding/Response/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Response_Abstract
 */
class Zend_Service_Ebay_Finding_Response_Histograms extends Zend_Service_Ebay_Finding_Response_Abstract
{
    /**
     * Response container for aspect histograms.
     *
     * Aspect histograms are returned for categories that have been mapped to
     * domains only. In most cases, just leaf categories are mapped to domains,
     * but there are exceptions.
     *
     * @var Zend_Service_Ebay_Finding_Aspect_Histogram_Container
     */
    public $aspectHistogramContainer;

    /**
     * Response container for category histograms.
     *
     * This container is returned only when the specified category has children
     * categories.
     *
     * @var Zend_Service_Ebay_Finding_Category_Histogram_Container
     */
    public $categoryHistogramContainer;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;


        $node = $this->_xPath->query(".//$ns:aspectHistogramContainer[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_Aspect_Histogram_Container
             */
            #require_once 'Zend/Service/Ebay/Finding/Aspect/Histogram/Container.php';
            $this->aspectHistogramContainer = new Zend_Service_Ebay_Finding_Aspect_Histogram_Container($node);
        }

        $node = $this->_xPath->query(".//$ns:categoryHistogramContainer[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_Category_Histogram_Container
             */
            #require_once 'Zend/Service/Ebay/Finding/Category/Histogram/Container.php';
            $this->categoryHistogramContainer = new Zend_Service_Ebay_Finding_Category_Histogram_Container($node);
        }
    }
}
