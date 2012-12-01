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
 * @package     Mage_Paygate
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Enter description here ...
 *
 * @method Mage_Paygate_Model_Resource_Authorizenet_Debug _getResource()
 * @method Mage_Paygate_Model_Resource_Authorizenet_Debug getResource()
 * @method string getRequestBody()
 * @method Mage_Paygate_Model_Authorizenet_Debug setRequestBody(string $value)
 * @method string getResponseBody()
 * @method Mage_Paygate_Model_Authorizenet_Debug setResponseBody(string $value)
 * @method string getRequestSerialized()
 * @method Mage_Paygate_Model_Authorizenet_Debug setRequestSerialized(string $value)
 * @method string getResultSerialized()
 * @method Mage_Paygate_Model_Authorizenet_Debug setResultSerialized(string $value)
 * @method string getRequestDump()
 * @method Mage_Paygate_Model_Authorizenet_Debug setRequestDump(string $value)
 * @method string getResultDump()
 * @method Mage_Paygate_Model_Authorizenet_Debug setResultDump(string $value)
 *
 * @category    Mage
 * @package     Mage_Paygate
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paygate_Model_Authorizenet_Debug extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('Mage_Paygate_Model_Resource_Authorizenet_Debug');
    }
}
