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
namespace Magento\PubSub\Job;

class QueueHandler
{
    /**
     * @var \Magento\PubSub\Job\QueueReaderInterface
     */
    protected $_jobQueueReader;

    /**
     * @var \Magento\PubSub\Job\QueueWriterInterface
     */
    protected $_jobQueueWriter;

    /**
     * @var \Magento\Outbound\TransportInterface
     */
    protected $_transport;

    /**
     * @var \Magento\Outbound\Message\FactoryInterface
     */
    protected $_messageFactory;

    /**
     * @param \Magento\PubSub\Job\QueueReaderInterface $jobQueueReader
     * @param \Magento\PubSub\Job\QueueWriterInterface $jobQueueWriter
     * @param \Magento\Outbound\TransportInterface $transport
     * @param \Magento\Outbound\Message\FactoryInterface $messageFactory
     */
    public function __construct(
        \Magento\PubSub\Job\QueueReaderInterface $jobQueueReader,
        \Magento\PubSub\Job\QueueWriterInterface $jobQueueWriter,
        \Magento\Outbound\TransportInterface $transport,
        \Magento\Outbound\Message\FactoryInterface $messageFactory
    ) {
        $this->_jobQueueReader = $jobQueueReader;
        $this->_jobQueueWriter = $jobQueueWriter;
        $this->_transport = $transport;
        $this->_messageFactory = $messageFactory;
    }

    /**
     * Process the queue of jobs
     * @return null
     */
    public function handle()
    {
        $job = $this->_jobQueueReader->poll();
        while (!is_null($job)) {
            $event = $job->getEvent();
            $message = $this->_messageFactory->create($job->getSubscription()->getEndpoint(),
                $event->getTopic(), $event->getBodyData());
            $response = $this->_transport->dispatch($message);
            if ($response->isSuccessful()) {
                $job->complete();
            } else {
                $job->handleFailure();
                $this->_jobQueueWriter->offer($job);
            }
            $job = $this->_jobQueueReader->poll();
        }
    }
}
