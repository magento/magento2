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
namespace Magento\Webhook\Model\Event;

/**
 * \Magento\Webhook\Model\Event\QueueWriter
 *
 * @magentoDbIsolation enabled
 */
class QueueWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDbIsolation enabled
     */
    public function testOfferWebhookEvent()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        // New collection must be created to avoid interference between QueueReader tests
        $collection =  $objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');
        $readerArgs = array('collection' => $collection);

        $bodyData = array('webhook', 'event', 'body', 'data');
        /** @var \Magento\Webhook\Model\Event\QueueWriter $queueWriter */
        $queueWriter = $objectManager->create('Magento\Webhook\Model\Event\QueueWriter');
        /** @var \Magento\Webhook\Model\Event $event */
        $event = $objectManager->create('Magento\Webhook\Model\Event')
            ->setBodyData($bodyData);
        $queueWriter->offer($event);
        /** @var \Magento\Webhook\Model\Event\QueueReader $queueReader */
        $queueReader = $objectManager->create('Magento\Webhook\Model\Event\QueueReader', $readerArgs);

        $this->assertEquals($event->getBodyData(), $queueReader->poll()->getBodyData());
        // Make sure poll returns null after queue is empty
        $this->assertNull($queueReader->poll());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testOfferMagentoEvent()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        // New collection must be created to avoid interference between QueueReader tests
        $collection =  $objectManager->create('Magento\Webhook\Model\Resource\Event\Collection');
        $readerArgs = array('collection' => $collection);

        $bodyData = array('magento', 'event', 'body', 'data');
        $topic = 'some topic';
        $eventArgs = array(
            'bodyData' => $bodyData,
            'topic' => $topic
        );

        /** @var \Magento\Webhook\Model\Event\QueueWriter $queueWriter */
        $queueWriter = $objectManager->create('Magento\Webhook\Model\Event\QueueWriter');
        /** @var \Magento\Webhook\Model\Event $magentoEvent */
        $magentoEvent = $objectManager->create('Magento\PubSub\Event', $eventArgs);
        $queueWriter->offer($magentoEvent);
        /** @var \Magento\Webhook\Model\Event\QueueReader $queueReader */
        $queueReader = $objectManager->create('Magento\Webhook\Model\Event\QueueReader', $readerArgs);

        $this->assertEquals($magentoEvent->getBodyData(), $queueReader->poll()->getBodyData());
        // Make sure poll returns null after queue is empty
        $this->assertNull($queueReader->poll());
    }
}
