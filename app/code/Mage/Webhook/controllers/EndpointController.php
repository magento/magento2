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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 *
 * Test subscriber endpoint controller
 * TODO: Use it only for testing, and remove it later
 *
 */
class Mage_Webhook_EndpointController extends Mage_Core_Controller_Front_Action
{
    /**
     * Send SOAP customer create request to the Magento using existent Magento SOAP authentication
     */
    public function soaptestAction()
    {
        $client = new SoapClient('http://magento-1.12.0.1.loc/api/v2_soap?wsdl=1');
        $session = $client->login('test_user', '111111');

        $newCustomer = array(
            'firstname'  => 'Soap Firstname ' . Mage::helper('Mage_Core_Helper_Data')->uniqHash(),
            'lastname'   => 'Soap Lastname ' . Mage::helper('Mage_Core_Helper_Data')->uniqHash(),
            'email'      => 'email' . Mage::helper('Mage_Core_Helper_Data')->uniqHash() . '@example.com',
            'password_hash'   => 'qa123123',
            'store_id'   => 1,
            'website_id' => 1
        );

        $newCustomerId = $client->customerCustomerCreate($session, $newCustomer);

        var_dump($newCustomerId);
    }

    /**
     * Send REST customer create request to the Magento REST Server using new OAuth authentication
     */
    public function resttestAction()
    {
        $subscriberId = $this->getRequest()->getParam('id');
        $subscriber = Mage::getModel('Mage_Webhook_Model_Subscriber')->load($subscriberId);

        $request = Mage::getModel('Mage_Webhook_Model_Transport_Http_Request')
            ->setMethod(Zend_Http_Client::POST)
            ->setUrl(str_replace('/index.php', '', Mage::getBaseUrl()).'api/rest/customers')
            ->setHeaders(array('Content-Type' => 'application/xml'))
            ->setBody('<?xml version="1.0"?>
                <magento_api>
                    <firstname>Earl'.Mage::helper('Mage_Core_Helper_Data')->uniqHash().'</firstname>
                    <lastname>Hickey'.Mage::helper('Mage_Core_Helper_Data')->uniqHash().'</lastname>
                    <password>qa123123</password>
                    <email>earl'.Mage::helper('Mage_Core_Helper_Data')->uniqHash().'@example.com</email>
                    <website_id>1</website_id>
                    <group_id>1</group_id>
                </magento_api>');

        $request = $subscriber->getAuthenticationModel()->signRequest($request, $subscriber);

        var_dump(Mage::getSingleton('Mage_Webhook_Model_Transport_Http_Request')->sendRequest($request));
    }

    public function oauthrequestAction()
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->setBody('oauth_token=hh5s93j4hdidpolahh5s93j4hdidpola&oauth_token_secret=hdhd0244k9j7ao03hdhd0244k9j7ao03&oauth_callback_confirmed=true');
    }

    public function oauthauthorizeAction()
    {
        echo '
            <h3>Login to the 3rd party service</h3>
             <form action="' . Mage::getModel('Mage_Core_Model_Url')->getUrl('*/*/submitform') . '">
                <table border=0>
                <tr>
                <td><label for="login">Login</label></td>
                <td><input type="text" name="login"/><br></td>
                </tr><tr>
                <td><label for="password">Password</label></td>
                <td><input type="password" name="password"/><br></td>
                </tr>
                <tr><td></td><td>
                <input type="submit" value="Submit">
                </td></tr>
                </table>
            </form>';
    }

    public function submitformAction()
    {
        $callbackUrl = Mage::getBaseUrl().'backend/admin/webhook_oauth/callback';

        $this->_redirectUrl($callbackUrl . '?oauth_token=hh5s93j4hdidpolahh5s93j4hdidpola&oauth_verifier=hfdp7dh39dks9884hfdp7dh39dks9884');
    }

    public function oauthaccessAction()
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->setBody('oauth_token=nnch734d00sl2jdknnch734d00sl2jdk&oauth_token_secret=pfkkdhi9sl3r4s00pfkkdhi9sl3r4s00');
    }

    public function consumercreatedAction()
    {
        // it's enought to respond HTTP 200 OK
    }
}

