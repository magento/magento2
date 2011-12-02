<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_GoogleOptimizer
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GoogleOptimizer Front Controller
 *
 * @category   Mage
 * @package    Mage_GoogleOptimizer
 * @name       Mage_GoogleOptimizer_Adminhtml_Googleoptimizer_IndexController
 * @author     Magento Core Team <core@magentocommerce.com>
*/
class Mage_GoogleOptimizer_Adminhtml_Googleoptimizer_IndexController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Retrieve js scripts by parsing remote Google Optimizer page
     */
    public function codesAction()
    {
        if ($this->getRequest()->getQuery('url')) {
            $client = new Varien_Http_Client($this->getRequest()->getQuery('url'));
            $response = $client->request(Varien_Http_Client::GET);
            $result = array();
            if (preg_match_all('/<textarea[^>]*id="([_a-zA-Z0-9]+)"[^>]*>([^<]+)<\/textarea>/', $response->getRawBody(), $matches)) {
                $c = count($matches[1]);
                for ($i = 0; $i < $c; $i++) {
                    $id = $matches[1][$i];
                    $code = $matches[2][$i];
                    $result[$id] = $code;
                }
            }
            $this->getResponse()->setBody( Mage::helper('Mage_Core_Helper_Data')->jsonEncode($result) );
        }
    }
}
