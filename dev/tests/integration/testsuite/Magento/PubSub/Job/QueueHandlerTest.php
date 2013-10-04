<?php
/**
 * \Magento\PubSub\Job\QueueHandler
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
namespace Magento\PubSub\Job;

/**
 * @magentoDbIsolation enabled
 */
class QueueHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\ObjectManager */
    private $_objectManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject  */
    private $_transportMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_responseMock;

    /** @var  \Magento\Webhook\Model\Event */
    private $_event;

    /** @var  \Magento\Webhook\Model\Subscription */
    private $_subscription;
    
    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // Must mock transport to avoid actual network actions
        $this->_transportMock = $this->getMockBuilder('Magento\Outbound\Transport\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_responseMock = $this->getMockBuilder('Magento\Outbound\Transport\Http\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_transportMock->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValue($this->_responseMock));

        /** \Magento\Webapi\Model\Acl\User $user */
        $user = $this->_objectManager->create('Magento\Webapi\Model\Acl\User')
            ->setSecret('shhh...')
            ->setApiKey(uniqid())
            ->save();

        /** @var \Magento\Webhook\Model\Event $_event */
        $this->_event = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Event')
            ->setTopic('topic')
            ->setBodyData(array('body data'))
            ->save();
        /** @var \Magento\Webhook\Model\Subscription $_subscription */
        $this->_subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription')
            ->setFormat('json')
            ->setAuthenticationType('hmac')
            ->setApiUserId($user->getId())
            ->save();
    }
    
    /**
     * Test the main flow of event queue handling given a successful job
     */
    public function testHandleSuccess()
    {
        $this->_responseMock->expects($this->any())
            ->method('isSuccessful')
            ->will($this->returnValue(true));

        $queueWriter = $this->_objectManager->create('Magento\PubSub\Job\QueueWriterInterface');

        /** @var \Magento\Webhook\Model\Job $job */
        $job = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Webhook\Model\Job');
        $job->setEventId($this->_event->getId());
        $job->setSubscriptionId($this->_subscription->getId());
        $jobId = $job->save()
            ->getId();
        $queueWriter->offer($job);

        // Must clear collection to avoid interaction with other tests
        /** @var \Magento\Webhook\Model\Resource\Job\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Job\Collection');
        $collection->removeAllItems();
        $queueArgs = array(
            'collection' => $collection
        );

        $queueReader = $this->_objectManager->create('Magento\PubSub\Job\QueueReaderInterface', $queueArgs);
        $queueHandlerArgs = array(
            'jobQueueReader' => $queueReader,
            'jobQueueWriter' => $queueWriter,
            'transport' => $this->_transportMock
        );

        /** @var \Magento\PubSub\Job\QueueHandler $queueHandler */
        $queueHandler = $this->_objectManager->create('Magento\PubSub\Job\QueueHandler', $queueHandlerArgs);
        $queueHandler->handle();
        $loadedJob = $this->_objectManager->create('Magento\Webhook\Model\Job')
            ->load($jobId);

        $this->assertEquals(\Magento\PubSub\JobInterface::STATUS_SUCCEEDED, $loadedJob->getStatus());
    }

    public function testHandleFailure()
    {
        $this->_responseMock->expects($this->any())
            ->method('isSuccessful')
            ->will($this->returnValue(false));

        $queueWriter = $this->_objectManager->create('Magento\PubSub\Job\QueueWriterInterface');

        /** @var \Magento\Webhook\Model\Job $job */
        $job = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Webhook\Model\Job');
        $job->setEventId($this->_event->getId());
        $job->setSubscriptionId($this->_subscription->getId());
        $jobId = $job->save()
            ->getId();
        $queueWriter->offer($job);

        // Must clear collection to avoid interaction with other tests
        /** @var \Magento\Webhook\Model\Resource\Job\Collection $collection */
        $collection = $this->_objectManager->create('Magento\Webhook\Model\Resource\Job\Collection');
        $collection->removeAllItems();
        $queueArgs = array(
            'collection' => $collection
        );

        $queueReader = $this->_objectManager->create('Magento\PubSub\Job\QueueReaderInterface', $queueArgs);
        $queueHandlerArgs = array(
            'jobQueueReader' => $queueReader,
            'jobQueueWriter' => $queueWriter,
            'transport' => $this->_transportMock
        );

        /** @var \Magento\PubSub\Job\QueueHandler $queueHandler */
        $queueHandler = $this->_objectManager->create('Magento\PubSub\Job\QueueHandler', $queueHandlerArgs);
        $queueHandler->handle();
        $loadedJob = $this->_objectManager->create('Magento\Webhook\Model\Job')
            ->load($jobId);

        $this->assertEquals(\Magento\PubSub\JobInterface::STATUS_RETRY, $loadedJob->getStatus());
    }
}
