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
 * @version    $Id: Result.php 22804 2010-08-08 05:08:05Z renanbr $
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
class Zend_Service_Ebay_Finding_Search_Result extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * Container for the data of a single item that matches the search criteria.
     *
     * @var Zend_Service_Ebay_Finding_Search_Item_Set
     */
    public $item;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;


        $nodes = $this->_xPath->query(".//$ns:item", $this->_dom);
        if ($nodes) {
            /**
             * @see Zend_Service_Ebay_Finding_Search_Item_Set
             */
            #require_once 'Zend/Service/Ebay/Finding/Search/Item/Set.php';
            $this->item = new Zend_Service_Ebay_Finding_Search_Item_Set($nodes);
        }
    }
}
