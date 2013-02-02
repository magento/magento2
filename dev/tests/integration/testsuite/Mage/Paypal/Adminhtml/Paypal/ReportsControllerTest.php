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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Paypal_Adminhtml_Paypal_ReportsControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * @magentoConfigFixture current_store paypal/fetch_reports/active 1
     * @magentoConfigFixture current_store paypal/fetch_reports/ftp_ip 127.0.0.1
     * @magentoConfigFixture current_store paypal/fetch_reports/ftp_path /tmp
     * @magentoConfigFixture current_store paypal/fetch_reports/ftp_login login
     * @magentoConfigFixture current_store paypal/fetch_reports/ftp_password password
     * @magentoConfigFixture current_store paypal/fetch_reports/ftp_sandbox 0
     * @magentoDbIsolation enabled
     */
    public function testFetchAction()
    {
        $this->dispatch('backend/admin/paypal_reports/fetch');
        /** @var $session Mage_Backend_Model_Session */
        $session = Mage::getSingleton('Mage_Backend_Model_Session');
        $this->assertEquals(1, $session->getMessages()->count());
        /** @var $message Mage_Core_Model_Message_Error */
        foreach ($session->getMessages() as $message) {
            $this->assertInstanceOf('Mage_Core_Model_Message_Error', $message);
            $this->assertContains('login@127.0.0.1', $message->getText());
        }
    }
}
