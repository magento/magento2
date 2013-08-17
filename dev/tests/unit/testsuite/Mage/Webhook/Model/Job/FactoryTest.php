<?php
/**
 * Mage_Webhook_Model_Job_Factory
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
class Mage_Webhook_Model_Job_FactoryTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webhook_Model_Job_Factory */
    private $_jobFactory;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $_mockObjectManager;

    public function setUp()
    {
        $this->_mockObjectManager = $this->getMockBuilder('Magento_ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_jobFactory = new Mage_Webhook_Model_Job_Factory($this->_mockObjectManager);
    }

    public function testCreate()
    {
        $subscription = $this->getMockBuilder('Magento_PubSub_SubscriptionInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder('Magento_PubSub_EventInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $job = 'JOB';
        $this->_mockObjectManager->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('Mage_Webhook_Model_Job'),
                $this->equalTo(
                    array(
                         'data' => array(
                             'event'        => $event,
                             'subscription' => $subscription,
                             'status'       => Magento_PubSub_JobInterface::READY_TO_SEND
                         ))
                )
            )
            ->will($this->returnValue($job));
        $this->assertSame($job, $this->_jobFactory->create($subscription, $event));
    }
}