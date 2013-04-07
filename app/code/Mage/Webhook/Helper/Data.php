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

class Mage_Webhook_Helper_Data extends Mage_Core_Helper_Abstract
{


    protected $_objectManager;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Mage_Core_Helper_Context $context, Magento_ObjectManager $objectManager)
    {
        parent::__construct($context);
        $this->_objectManager = $objectManager;
    }

    /**
     * Dispatch hook with specified data.
     *
     * @param string $topic topic of the event
     * @param array $data data for the hook
     * @return Mage_Webhook_Helper_Data
     */
    public function dispatchEvent($topic, array $data)
    {
        try {
            $config = Mage::getConfig()->getNode('global/webhook/mappings');
            $queue = Mage::getModel('Mage_Webhook_Model_Event_Queue');
            $mapperFactory = Mage::getModel('Mage_Webhook_Model_Mapper_Factory'); // , $this->_objectManager);

            $eventFactory = $this->_objectManager
                ->get('Mage_Webhook_Model_Event_Factory', array($this->_objectManager));
            $marshaller = Mage::getModel(
                'Mage_Webhook_Model_Event_Marshaller',
                array(
                    'mappingsConfig'    => $config,
                    'queue'             => $queue,
                    'mapperFactory'     => $mapperFactory,
                    'eventFactory'      => $eventFactory
                )
            );
            $marshaller->marshal($topic, $data);

            // TODO: Remove the 3 lines below and just rely on cronjob in future
            $observer = Mage::getModel('Mage_Webhook_Model_Observer');
            $observer->processEventsToDispatch();
            $observer->processDispatchJobs();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Dispatches Callback and returns the result
     *
     * @param $topic
     * @param array $data
     * @return array
     */
    public function dispatchCallback($extensionId, $topic, array $data)
    {
        $result = null;
        $subscriber = $this->_getSingleActiveSubscriber($extensionId, $topic);

        try {
            $config = Mage::getConfig()->getNode('global/webhook/mappings');
            $mappingFactory = $this->_objectManager->create('Mage_Webhook_Model_Mapper_Factory', array($this->_objectManager));
            $mapperFactory = $mappingFactory->getMapperFactory($subscriber->getMapping(), $config);
            $mapper = $mapperFactory->getMapper(
                $topic, $data, $config->descend($subscriber->getMapping() . "/options")
            );

            /** @var $eventFactory Mage_Webhook_Model_Event_Factory */
            $eventData = array(
                'mapping' => $subscriber->getMapping(),
                'bodyData' => $mapper->getData(),
                'headers' => $mapper->getHeaders(),
                'topic' => $topic
            );

            $eventFactory = $this->_objectManager
                ->get('Mage_Webhook_Model_Event_Factory', array($this->_objectManager));
            $event = $eventFactory->createEvent(Mage_Webhook_Model_Event_Interface::EVENT_TYPE_CALLBACK, $eventData);

            /** @var $message Mage_Webhook_Model_Message_Interface */
            $message = $this->_objectManager->create('Mage_Webhook_Model_Job_Dispatcher')
                ->dispatchCallback($event, $subscriber);
            $result = $message->getResponseData();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $result;
    }

    protected function _getSingleActiveSubscriber($extensionId, $topic)
    {
        /** @var $subscriberCollection Mage_Webhook_Model_Resource_Subscriber_Collection */
        $subscriber = $this->_objectManager
            ->create('Mage_Webhook_Model_Resource_Subscriber_Collection')
            ->addExtensionIdFilter($extensionId)
            ->addTopicFilter($topic)
            ->addIsActiveFilter(true)
            ->getSingleSubscriber();
        if (null === $subscriber) {
            throw Mage::exception('Mage_Webhook', 'No subscriber found');
        }
        return $subscriber;
    }


    public function generateRandomString($length)
    {
        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('Mage_Core_Helper_Data');
        return $coreHelper->getRandomString($length, Mage_Core_Helper_Data::CHARS_DIGITS . Mage_Core_Helper_Data::CHARS_LOWERS);
    }
}
