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
 * @package     Mage_Rss
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Rss
 */
class Mage_Rss_OrderControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    public function testNewActionNonLoggedUser()
    {
        $this->markTestIncomplete('Incomplete until Mage_Core_Helper_Http stops exiting script for non-logged user');
        $this->dispatch('rss/order/new/');
    }

    public function testNewActionLoggedUser()
    {
        $admin = new Mage_Admin_Model_User;
        $admin->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $session = Mage::getSingleton('Mage_Rss_Model_Session');
        $session->setAdmin($admin);

        $adminSession = Mage::getSingleton('Mage_Admin_Model_Session');
        $adminSession->setUpdatedAt(time())
            ->setUser($admin);

        $this->dispatch('rss/order/new/');

        $body = $this->getResponse()->getBody();
        $this->assertNotEmpty($body);

        $response = Mage::app()->getResponse();
        $code = $response->getHttpResponseCode();
        $this->assertFalse(($code >= 300) && ($code < 400));

        $xmlContentType = false;
        $headers = $response->getHeaders();
        foreach ($headers as $header) {
            if ($header['name'] != 'Content-Type') {
                continue;
            }
            if (strpos($header['value'], 'text/xml') !== false) {
                $xmlContentType = true;
            }
        }
        $this->assertTrue($xmlContentType, 'Rss document should output xml header');

        $body = $response->getBody();
        $this->assertContains('<rss', $body);
    }
}
