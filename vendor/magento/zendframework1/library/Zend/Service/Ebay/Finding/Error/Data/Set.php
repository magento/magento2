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
 * @version    $Id: Set.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_Ebay_Finding_Set_Abstract
 */
#require_once 'Zend/Service/Ebay/Finding/Set/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Set_Abstract
 */
class Zend_Service_Ebay_Finding_Error_Data_Set extends Zend_Service_Ebay_Finding_Set_Abstract
{
    /**
     * Implement SeekableIterator::current()
     *
     * @return Zend_Service_Ebay_Finding_Error_Data
     */
    public function current()
    {
        // check node
        $node = $this->_nodes->item($this->_key);
        if (!$node) {
            return null;
        }

        /**
         * @see Zend_Service_Ebay_Finding_Error_Data
         */
        #require_once 'Zend/Service/Ebay/Finding/Error/Data.php';
        return new Zend_Service_Ebay_Finding_Error_Data($node);
    }
}
