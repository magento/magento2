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
 * @version    $Id: Keywords.php 22824 2010-08-09 18:59:54Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Finding_Response_Abstract
 */
#require_once 'Zend/Service/Ebay/Finding/Response/Abstract.php';

/**
 * @see Zend_Service_Ebay_Finding
 */
#require_once 'Zend/Service/Ebay/Finding.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Response_Abstract
 */
class Zend_Service_Ebay_Finding_Response_Keywords extends Zend_Service_Ebay_Finding_Response_Abstract
{
    /**
     * Contains a spell-checked version of the submitted keywords. If no
     * recommended spelling can be identified for the submitted keywords, the
     * response contains a warning to that effect and an empty keywords field
     * is returned.
     *
     * @var string
     */
    public $keywords;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->keywords = $this->_query(".//$ns:keywords[1]", 'string');
    }

    /**
     * @param  Zend_Service_Ebay_Finding $proxy
     * @param  Zend_Config|array         $options
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function findItems(Zend_Service_Ebay_Finding $proxy, $options = null)
    {
        // prepare options
        $options = Zend_Service_Ebay_Abstract::optionsToArray($options);
        $options = $options + $this->_options;

        // find items
        return $proxy->findItemsByKeywords($this->keywords, $options);
    }
}
