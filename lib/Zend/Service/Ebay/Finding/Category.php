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
 * @version    $Id: Category.php 22824 2010-08-09 18:59:54Z renanbr $
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
class Zend_Service_Ebay_Finding_Category extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * The unique ID of a category on the specified eBay site.
     *
     * @var string
     */
    public $categoryId;

    /**
     * Display name of a category as it appears on the eBay Web site.
     *
     * @var string
     */
    public $categoryName;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->categoryId   = $this->_query(".//$ns:categoryId[1]", 'string');
        $this->categoryName = $this->_query(".//$ns:categoryName[1]", 'string');
    }

    /**
     * @param  Zend_Service_Ebay_Finding $proxy
     * @param  Zend_Config|array         $options
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function findItems(Zend_Service_Ebay_Finding $proxy, $options = null)
    {
        return $proxy->findItemsByCategory($this->categoryId, $options);
    }
}
