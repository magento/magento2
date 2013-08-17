<?php
/**
 * Dispatches HTTP messages derived from job queue and handles the responses
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
 * @category    Magento
 * @package     Magento_PubSub
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_PubSub_Job_QueueHandler
{
    /**
     * @var Magento_PubSub_Job_QueueReaderInterface
     */
    protected $_jobQueue;

    /**
     * @var Magento_Outbound_TransportInterface
     */
    protected $_transport;

    /**
     * @var Magento_Outbound_Message_FactoryInterface
     */
    protected $_messageFactory;

    /**
     * @param Magento_PubSub_Job_QueueReaderInterface $jobQueue
     * @param Magento_Outbound_TransportInterface $transport
     * @param Magento_Outbound_Message_FactoryInterface $messageFactory
     */
    public function __construct(
        Magento_PubSub_Job_QueueReaderInterface $jobQueue,
        Magento_Outbound_TransportInterface $transport,
        Magento_Outbound_Message_FactoryInterface $messageFactory
    ) {
        $this->_jobQueue = $jobQueue;
        $this->_transport = $transport;
        $this->_messageFactory = $messageFactory;
    }

    /**
     * Process the queue of jobs
     * @return null
     */
    public function handle()
    {
        $job = $this->_jobQueue->poll();
        while (!is_null($job)) {
            $message = $this->_messageFactory->create($job->getSubscription(), $job->getEvent());
            $response = $this->_transport->dispatch($message);
            $job->handleResponse($response);
            $job = $this->_jobQueue->poll();
        }
    }
}