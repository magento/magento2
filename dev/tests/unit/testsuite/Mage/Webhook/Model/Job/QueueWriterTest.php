<?php
/**
 * Mage_Webhook_Model_Job_QueueWriter
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Job_QueueWriterTest extends PHPUnit_Framework_TestCase
{

    /** @var Mage_Webhook_Model_Job_QueueWriter */
    private $_jobQueue;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $_jobFactory;

    public function setUp()
    {
        $this->_jobFactory = $this->getMockBuilder('Mage_Webhook_Model_Job_Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $this->_jobQueue = new Mage_Webhook_Model_Job_QueueWriter($this->_jobFactory);
    }

    public function testOfferMagentoJob()
    {
        $magentoJob = $this->getMockBuilder('Mage_Webhook_Model_Job')
            ->disableOriginalConstructor()
            ->getMock();
        $magentoJob->expects($this->once())
            ->method('save');
        $result = $this->_jobQueue->offer($magentoJob);
        $this->assertEquals(null, $result);
    }

    public function testOfferNonMagentoJob()
    {
        $magentoJob = $this->getMockBuilder('Mage_Webhook_Model_Event')
            ->disableOriginalConstructor()
            ->getMock();
        $magentoJob->expects($this->once())
            ->method('save');

        $this->_jobFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($magentoJob));


        $job = $this->getMockBuilder('Magento_PubSub_JobInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $subscription = $this->getMockBuilder('Magento_PubSub_SubscriptionInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder('Magento_PubSub_EventInterface')
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