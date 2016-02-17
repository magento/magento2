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
 * @version    $Id: Storefront.php 22824 2010-08-09 18:59:54Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Finding_Abstract
 */
#require_once 'Zend/Service/Ebay/Finding/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Abstract
 */
class Zend_Service_Ebay_Finding_Storefront extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * The name of the seller's eBay Store.
     *
     * @var string
     */
    public $storeName;

    /**
     * The URL of the seller's eBay Store page.
     *
     * @var string
     */
    public $storeURL;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->storeName = $this->_query(".//$ns:storeName[1]", 'string');
        $this->storeURL  = $this->_query(".//$ns:storeURL[1]", 'string');
    }

    /**
     * @param  Zend_Service_Ebay_Finding $proxy
     * @param  Zend_Config|array         $options
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function findItems(Zend_Service_Ebay_Finding $proxy, $options = null)
    {
        return $proxy->findItemsInEbayStores($this->storeName, $options);
    }
}
