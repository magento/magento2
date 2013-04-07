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
class Webapi_Webhook_WebhookTest extends Magento_Test_Webservice_Rest_Admin
{
    const VERSON = 'v1';

    /**
     * WebHook service model instance
     *
     * @var Mage_WebHooksCore_Model_Subscriber
     */
    protected $_webhook;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();
        $this->_initWebhook();
    }

    /**
     * Init webhook service model instance
     *
     * @return Webapi_Webhook_WebhookTest
     */
    protected function _initWebhook()
    {
        if (null === $this->_webhook) {
            $this->_webhook = require dirname(__FILE__) . '/../../../fixture/_block/Webhook/Webhook.php';
            $this->_webhook->save();
            $this->addModelToDelete($this->_webhook, true);
        }

        return $this;
    }

    /**
     * Test creates WebHook
     *
     * @resourceOperation xcomMessengerWebhook::create
     */
    public function testCreate()
    {
        $body = array(
            'name' => $this->_randomString(),
            'endpoint_url' => 'http://test.com/endpoint/' . $this->_randomString(),
            'topics' => array("customer/created", "customer/updated","product/deleted", "product/created")
        );
        $response = $this->callPost(self::VERSON . '/webhooks', $body);
        $this->assertEquals(Mage_Webapi_Controller_Front_Rest::HTTP_CREATED, $response->getStatus());
        $this->assertNotNull($response->getHeader('Location'));
    }

    /**
     * Test retrieves existing WebHook data
     *
     * @resourceOperation xcomMessengerWebhook::get
     */
    public function testRetrieve()
    {
        $response = $this->callGet(self::VERSON . '/webhooks/' . $this->_webhook->getId());

        $this->assertEquals(Mage_Webapi_Controller_Front_Rest::HTTP_OK, $response->getStatus());

        $responseData = $response->getBody();
        $this->assertNotEmpty($responseData);
        $this->assertEquals($this->_webhook->getName(), $responseData['name']);
    }

    /**
     * Test retrieves not existing WebHook
     *
     * @resourceOperation xcomMessengerWebhook::get
     */
    public function testRetrieveUnavailableResource()
    {
        $response = $this->callGet(self::VERSON . '/webhooks/invalid_id');
        $this->assertEquals(Mage_Webapi_Exception::HTTP_NOT_FOUND, $response->getStatus());
    }

    /**
     * Test retrieves existing WebHooks
     *
     * @resourceOperation xcomMessengerWebhook::list
     */
    public function testRetrieveAll()
    {
        $response = $this->callGet(self::VERSON . '/webhooks');
        $this->assertEquals(Mage_Webapi_Controller_Front_Rest::HTTP_OK, $response->getStatus());
        $responseData = $response->getBody();
        $this->assertNotEmpty($responseData);
        $this->assertTrue(count($responseData)>0);
    }

    /**
     * Test update an existing WebHook
     *
     * @resourceOperation xcomMessengerWebhook::update
     */
    public function testUpdate()
    {
        $putData = array(
            'name' => $this->_randomString(),
            'endpoint_url' => 'http://test.com/endpoint/' . $this->_randomString(),
            'topics' => array("customer/created", "customer/updated")
        );
        $response = $this->callPut(self::VERSON . '/webhooks/' . $this->_webhook->getId(), $putData);

        $this->assertEquals(Mage_Webapi_Controller_Front_Rest::HTTP_OK, $response->getStatus());

        /**
         * Reload webhook
         *
         * @@var $updatedWebhook Mage_Webhook_Model_Subscriber
         */
        $updatedWebhook = Mage::getModel('Mage_Webhook_Model_Subscriber');
        $updatedWebhook->load($this->_webhook->getId());

        foreach ($putData as $field => $expectedValue) {
            if ($field === 'topics') {
                $this->assertEquals($expectedValue, $updatedWebhook->getTopics());
            } else {
                $this->assertEquals($expectedValue, $updatedWebhook->getData($field));
            }
        }
    }

    /**
     * Test deletes an existing WebHook
     *
     * @resourceOperation xcomMessengerWebhook::delete
     */
    public function testDelete()
    {
        $response = $this->callDelete(self::VERSON . '/webhooks/' . $this->_webhook->getId());

        $this->assertEquals(Mage_Webapi_Controller_Front_Rest::HTTP_OK, $response->getStatus());

        /** @var $model Mage_Webhook_Model_Subscriber */
        $model = Mage::getModel('Mage_Webhook_Model_Subscriber')->load($this->_webhook->getId());
        $this->assertEmpty($model->getId());
    }

    /**
     * Test deletes not existing webhook
     *
     * @resourceOperation xcomMessengerWebhook::delete
     */
    public function testDeleteUnavailableResource()
    {
        $response = $this->callDelete(self::VERSON . '/webhooks/invalid_id');
        $this->assertEquals(Mage_Webapi_Exception::HTTP_NOT_FOUND, $response->getStatus());
    }

    private function _randomString($length = 6) {
        $str = "";
        $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }
}
