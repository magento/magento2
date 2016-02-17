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
 * @version    $Id: Aspect.php 22791 2010-08-04 16:11:47Z renanbr $
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
class Zend_Service_Ebay_Finding_Aspect extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * Container that returns the name of the respective aspect value and the
     * histogram (the number of available items) that share that item
     * characteristic.
     *
     * @var Zend_Service_Ebay_Finding_Aspect_Histogram_Value_Set
     */
    public $valueHistogram;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->_attributes['valueHistogram'] = array(
            'valueName' => $this->_query(".//$ns:valueHistogram/@valueName", 'string', true)
        );

        $nodes = $this->_xPath->query(".//$ns:valueHistogram", $this->_dom);
        if ($nodes->length > 0) {
            /**
             * @see Zend_Service_Ebay_Finding_Aspect_Histogram_Value_Set
             */
            #require_once 'Zend/Service/Ebay/Finding/Aspect/Histogram/Value/Set.php';
            $this->valueHistogram = new Zend_Service_Ebay_Finding_Aspect_Histogram_Value_Set($nodes);
        }
    }
}
