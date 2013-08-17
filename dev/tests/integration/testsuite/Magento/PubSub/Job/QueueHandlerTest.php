<?php
/**
 * Magento_PubSub_EventManager
 *
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

/**
 * @magentoDbIsolation enabled
 */
class Magento_PubSub_Job_QueueHandlerTests extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_PubSub_Job_QueueHandler
     */
    protected $_model;

    /**
     * Initialize the model
     */
    public function setUp()
    {
        Mage::getObjectManager()->configure(array(
            'Mage_Core_Model_Config_Base' => array(
                'parameters' => array(
                    'sourceData' => __DIR__ . '/../_files/config.xml',
                ),
            ),
            'Mage_Webhook_Model_Resource_Subscription' => array(
                'parameters' => array(
                    'config' => array('instance' => 'Mage_Core_Model_Config_Base'),
                ),
            )
        ));

        /** @var Mage_Webhook_Model_Resource_Event_Collection $eventCollection */
        $eventCollection = Mage::getObjectManager()->create('Mage_Webhook_Model_Resource_Event_Collection')
            ->addFieldToFilter('status', Magento_PubSub_EventInterface::READY_TO_SEND);
        /** @var array $event */
        $events = $eventCollection->getItems();
        /** @var Mage_Webhook_Model_Event $event */
        foreach ($events as $event) {
            $event->markAsProcessed();
            $event->save();
        }
        /** @var $factory Mage_Webhook_Model_Event_Factory */
        $factory = Mage::getObjectManager()->create('Magento_PubSub_Event_FactoryInterface');

        /** @var $event Mage_Webhook_Model_Event */
        $factory->create('testinstance/created', array(
            'testKey1' => 'testValue1'
        ))->save();

        $factory->create('testinstance/updated', array(
            'testKey2' => 'testValue2'
        ))->save();

        $webApiUser = Mage::getObjectManager()->create('Mage_Webapi_Model_Acl_User')
            ->setData('api_key', 'test')
            ->setData('secret', 'secret')
            ->save();

        $endpoint = Mage::getObjectManager()->create('Mage_Webhook_Model_Endpoint')
            ->setData(
                array(
                    'endpoint_url' => 'http://test.domain.com/',
                    'format' => 'json',
                    'authentication_type' => 'hmac',
                    'api_user_id' => $webApiUser->getId(),
                    'timeout_in_secs' => 20,
                )
            )
            ->save();

        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getObjectManager()->create('Mage_Webhook_Model_Subscription');
        $subscription->setData(
            array(
                'name' => 'test',
                'status' => 1,
                'version' => 1,
                'alias' => 'test',
                'topics' => array(
                    'testinstance/created',
                    'testinstance/updated'
                ),
                'endpoint_id' => $endpoint->getId(),
            ))->save();

        Mage::getObjectManager()->get('Magento_PubSub_Event_QueueHandler')
            ->handle();

        /** @var $transport Magento_Outbound_Transport_Http */
        $transport = Mage::getObjectManager()->create('Magento_Outbound_Transport_Http', array(
             'curl' => $this->_initHttpAdapter()
        ));

        $this->_model = Mage::getObjectManager()->create('Magento_PubSub_Job_QueueHandler', array(
            'transport' => $transport
        ));
    }

    /**
     * Test the main flow of event queue handling
     */
    public function testHandle()
    {
        $this->_model->handle();
    }

    /**
     * @return Varien_Http_Adapter_Curl
     */
    protected function _initHttpAdapter()
    {
        /** @var $httpAdapterMock Varien_Http_Adapter_Curl */
        $httpAdapterMock = $this->getMock('Varien_Http_Adapter_Curl', array('setConfig', 'write', 'read'));
        $config = array(
            'verifypeer' => TRUE,
            'verifyhost' => 2,
            'timeout' => 20
        );
        $httpAdapterMock->expects($this->at(0))
            ->method('setConfig')
            ->with($config);

        $httpAdapterMock->expects($this->at(1))
            ->method('write')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo('http://test.domain.com/'),
                $this->equalTo('1.1'),
                $this->anything(),
                $this->equalTo('{"testKey1":"testValue1"}')
            );

        $response = 'HTTP/1.1 200 OK
Content-Type: text/plain; charset=utf-8
Content-Length: 20

OK';
        $httpAdapterMock->expects($this->at(2))
            ->method('read')
            ->will($this->returnValue($response));

        $httpAdapterMock->expects($this->at(3))
            ->method('setConfig')
            ->with($config);

        $httpAdapterMock->expects($this->at(4))
            ->method('write')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo('http://test.domain.com/'),
                $this->equalTo('1.1'),
                $this->anything(),
                $this->equalTo('{"testKey2":"testValue2"}')
            );

        $httpAdapterMock->expects($this->at(5))
            ->method('read')
            ->will($this->returnValue($response));

        return $httpAdapterMock;
    }
}