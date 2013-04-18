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
class Mage_Webhook_Model_Observer
{
    /**
     * Default page size for response collection.
     */
    const PAGE_SIZE = 100;

    /** @var $_webapiEventHandlerFactory Mage_Webhook_Model_Webapi_EventHandler_Factory */
    protected $_webapiEventHandlerFactory;
    protected $_webapiEventHandler;


    public function __construct(Mage_Webhook_Model_Webapi_EventHandler_Factory $webapiEventHandlerFactory)
    {
        $this->_webapiEventHandlerFactory = $webapiEventHandlerFactory;
    }

    /**
     * Send the messages for each job.
     *
     * @return Mage_Webhook_Model_Observer
     */
    public function processDispatchJobs()
    {
        $jobsToDo = array(
            Mage_Webhook_Model_Dispatch_Job::READY_TO_SEND,
            Mage_Webhook_Model_Dispatch_Job::RETRY
        );

        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::getResourceModel('Mage_Webhook_Model_Resource_Dispatch_Job_Collection')
            ->setPageSize(self::PAGE_SIZE)
            ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC)
            ->addFieldToFilter('status', array('in' => $jobsToDo))
            ->addFieldToFilter('retry_at', array('to' => Varien_Date::formatDate(true), 'datetime' => true));


        if ($collection->getSize() > 0) {
            for ($i = 1; $i <= $collection->getLastPageNumber(); $i++) {
                $collection->setCurPage($i)
                    ->addLimitPage()
                    ->clear();

                foreach ($collection as $job) {
                    $job_dispatcher = Mage::getModel('Mage_Webhook_Model_Job_Dispatcher');
                    $job_dispatcher->dispatch($job);
                }
            }
        }

        return $this;
    }

    /**
     * Creates jobs to dispatch from unprocessed events.
     *
     * @return Mage_Webhook_Model_Observer
     */
    public function processEventsToDispatch()
    {
        $queue = Mage::getModel('Mage_Webhook_Model_Event_Queue');
        Mage::getModel('Mage_Webhook_Model_Job_Processor')->createJobsFromQueue($queue);

        return $this;
    }

    /**
     * Take a generic event and dispatch it by using the magento event topic as our webhook topic name.
     * @param $observer Varien_Event_Observer
     */
    public function dispatchEvent($observer)
    {
        $event = $observer->getEvent();
        $topic = $event->getName();
        $object = $event->getDataObject();

        /**
         * Thought was to read params from event in generic fashion.  Problem is we can't even get an array
         * Best bet is to read data from Observer and filter out 'event'.  Then turn that into an array which
         * could contain Varien Objects... so maybe wrap it all in a varien object.  Worth it?
         */
        $topicData = new Varien_Object($observer->getData());
        $topicData->unsetData('event');

        try {
            Mage::helper('Mage_Webhook_Helper_Data')->dispatchEvent($topic, array('object' => $topicData));
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Triggered after webapi user deleted
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Webhook_Model_Observer
     */
    public function afterWebapiUserDelete(Varien_Event_Observer $observer)
    {
        try{
            $collection = Mage::getResourceModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
            $subscribers = $collection->addFieldToFilter('api_user_id', array("null" => null))
                ->addFieldToFilter('status', array("neq" => Mage_Webhook_Model_Subscriber::STATUS_INACTIVE))
                ->getItems();
            if (count($subscribers) > 0) {
                foreach ($subscribers as $subscriber) {
                    $subscriber->load($subscriber->getId())->save();
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered after webapi user change
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Webhook_Model_Observer
     */
    public function afterWebapiUserChange(Varien_Event_Observer $observer)
    {
        $model = $observer->getEvent()->getObject();

        $this->_getWebapiEventHandler()->userChanged($model);
    }

    /**
     * Triggered after webapi role change
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Webhook_Model_Observer
     */
    public function afterWebapiRoleChange(Varien_Event_Observer $observer)
    {
        $model = $observer->getEvent()->getObject();

        $this->_getWebapiEventHandler()->roleChanged($model);
    }

    /**
     * Returns an event handler for events related to webapi
     * @return Mage_Webhook_Model_Webapi_EventHandler event handler
     */
    protected function _getWebapiEventHandler()
    {
        if (null === $this->_webapiEventHandler) {
            $this->_webapiEventHandler = $this->_webapiEventHandlerFactory->create();
        }
        return $this->_webapiEventHandler;
    }


    private function _debug($debuggable)
    {
        if (method_exists($debuggable, 'debug')) {
            Mage::log($debuggable->debug());
        } else {
            Mage::log(print_r($debuggable, true));
        }
    }
    

    
}
