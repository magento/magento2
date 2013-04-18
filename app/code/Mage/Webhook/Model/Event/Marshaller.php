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
 */
class Mage_Webhook_Model_Event_Marshaller implements Mage_Webhook_Model_Event_Marshaller_Interface
{
    /**
     * @var Mage_Core_Model_Config_Element
     */
    protected $_mappingsConfig;

    /**
     * @var Mage_Core_Model_Event_Queue
     */
    protected $_queue;

    /**
     * @var Mage_Webhook_Model_Event_Factory
     */
    protected $_eventFactory;

    /**
     * @var Mage_Webhook_Model_Mapper_Factory
     */
    protected $_mapperFactory;

    public function __construct(
        Mage_Core_Model_Config_Element $mappingsConfig,
        Mage_Webhook_Model_Event_Queue $queue,
        Mage_Webhook_Model_Mapper_Factory $mapperFactory,
        Mage_Webhook_Model_Event_Factory $eventFactory
    ) {
        $this->_mappingsConfig = $mappingsConfig;
        $this->_queue = $queue;
        $this->_mapperFactory = $mapperFactory;
        $this->_eventFactory = $eventFactory;
    }

    public function marshal($topic, array $data)
    {
        $isSuccessful = true;
        // serialize all of the different message mappings
        foreach ($this->_getMappingsForTopicSubscribers($topic) as $mapping) {
            try {
                $event = $this->getEvent($topic, $data, $mapping);

                if (!$this->_queue->offer($event)) {
                    $isSuccessful = false;
                }
            } catch (Exception $e) {
                Mage::logException($e);
                $isSuccessful = false;
            }
        }

        return $isSuccessful;
    }

    public function getEvent($topic, array $data, $mapping)
    {
        $mapperFactory = $this->_mapperFactory->getMapperFactory($mapping, $this->_mappingsConfig);
        $mapper = $mapperFactory->getMapper(
            $topic, $data, $this->_mappingsConfig->descend("$mapping/options")
        );

        $event = $this->getEventFactory()->createEvent(Mage_Webhook_Model_Event_Interface::EVENT_TYPE_INFORM, array())
            ->setTopic($mapper->getTopic())
            ->setBodyData($mapper->getData())
            ->setHeaders($mapper->getHeaders())
            ->setMapping($mapping);

        return $event;
    }

    public function getEventFactory()
    {
        return $this->_eventFactory;
    }

    protected function _getMappingsForTopicSubscribers($topic)
    {
        // treat $mappings as a set
        $mappings = array();

        // TODO: would really like to just query the database for all the unique mappings and have
        // the database do the work
        $subscribers = $this->_getSubscriberCollection()->addTopicFilter($topic)->addIsActiveFilter(true)->getItems();
        foreach ($subscribers as $subscriber) {
            $mappings[$subscriber->getMapping()] = true;
        }

        return array_keys($mappings);
    }

    /**
     * @return Mage_Webhook_Model_Subscriber_SubscriberCollection
     */
    protected function _getSubscriberCollection()
    {
        return Mage::getResourceModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
    }
}
