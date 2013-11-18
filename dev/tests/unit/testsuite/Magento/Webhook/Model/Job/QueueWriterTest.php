<?php
/**
 * \Magento\Webhook\Model\Job\QueueWriter
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
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Job;

class QueueWriterTest extends \PHPUnit_Framework_TestCase
{

    /** @var \Magento\Webhook\Model\Job\QueueWriter */
    private $_jobQueue;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_jobFactory;

    protected function setUp()
    {
        $this->_jobFactory = $this->getMockBuilder('Magento\Webhook\Model\Job\Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $this->_jobQueue = new \Magento\Webhook\Model\Job\QueueWriter($this->_jobFactory);
    }

    public function testOfferMagentoJob()
    {
        $magentoJob = $this->getMockBuilder('Magento\Webhook\Model\Job')
            ->disableOriginalConstructor()
            ->getMock();
        $magentoJob->expects($this->once())
            ->method('save');
        $result = $this->_jobQueue->offer($magentoJob);
        $this->assertEquals(null, $result);
    }

    public function testOfferNonMagentoJob()
    {
        $magentoJob = $this->getMockBuilder('Magento\Webhook\Model\Event')
            ->disableOriginalConstructor()
            ->getMock();
        $magentoJob->expects($this->once())
            ->method('save');

        $this->_jobFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($magentoJob));


        $job = $this->getMockBuilder('Magento\PubSub\JobInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $subscription = $this->getMockBuilder('Magento\PubSub\SubscriptionInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder('Magento\PubSub\EventInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $job->expects($this->once())
            ->method('getSubscription')
            ->will($this->returnValue($subscription));
        $job->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($event));
        $result = $this->_jobQueue->offer($job);
        $this->assertEquals(null, $result);
    }
}
