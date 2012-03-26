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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * HTTP response implementation that is used instead core one for testing
 */
class Magento_Test_Response extends Mage_Core_Controller_Response_Http
{
    /**
     * Prevent generating exceptions if headers are already sent
     *
     * Prevents throwing an exception in Zend_Controller_Response_Abstract::canSendHeaders()
     * All functionality that depend on headers validation should be covered with unit tests by mocking response.
     *
     * @param bool $throw
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canSendHeaders($throw = false)
    {
        return true;
    }

    public function sendResponse()
    {
        Mage::dispatchEvent('http_response_send_before', array('response'=>$this));
    }
}
