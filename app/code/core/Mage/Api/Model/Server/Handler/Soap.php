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
 * @package     Mage_Api
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservices server handler v2
 *
 * @category   Mage
 * @package    Mage_Api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Server_Handler_Soap extends Mage_Api_Model_Server_HandlerAbstract
{
    protected $_resourceSuffix = '_V2';

    /**
     * Interceptor for all interfaces
     *
     * @param string $function
     * @param array $args
     * @return mixed
     */

    public function __call($function, $args)
    {
        $sessionId = array_shift($args);
        $apiKey = '';
        $nodes = Mage::getSingleton('Mage_Api_Model_Config')->getNode('v2/resources_function_prefix')->children();
        foreach ($nodes as $resource => $prefix) {
            $prefix = $prefix->asArray();
            if (false !== strpos($function, $prefix)) {
                $method = substr($function, strlen($prefix));
                $apiKey = $resource . '.' . strtolower($method[0]) . substr($method, 1);
            }
        }
        return $this->_call($sessionId, $apiKey, $args);
    }
}
