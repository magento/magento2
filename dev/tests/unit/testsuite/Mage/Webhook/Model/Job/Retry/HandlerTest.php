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
class Mage_Webhook_Model_Job_HandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webhook_Model_Job
     */
    protected $_jobMock;

    /**
     * @var Mage_Webhook_Model_Job_Retry_Handler
     */
    protected $_handler;

    public function setUp()
    {
        parent::setUp();

        $this->_jobMock = $this->getMockBuilder('Mage_Webhook_Model_Dispatch_Job')
                               ->disableOriginalConstructor()
                               ->setMethods(array(
                                   'getRetryCount',
                                   'setRetryCount',
                                   'setRetryAt',
                                   'setUpdatedAt',
                                   'setStatus',
                                   'getEvent',
                                   'getSubscriber',
                                   'getStatus'
                                ))
                               ->getMock();

        $this->_handler = new Mage_Webhook_Model_Job_Retry_Handler();
    }

    /**
     * Tests that a job which has failed for the first 8 times is given another
     * chance.
     */
    public function testJobGiven8Retries()
    {
        $retryCount = 8;
        $this->_jobMock->expects($this->exactly($retryCount))
                       ->method('getRetryCount')
                       ->will($this->onConsecutiveCalls(0, 1, 2, 3, 4, 5, 6, 7));

        $this->_jobMock->expects($this->exactly($retryCount))
                       ->method('setRetryAt')
                       ->with($this->anything());
        $this->_jobMock->expects($this->exactly($retryCount))
                       ->method('setUpdatedAt')
                       ->with($this->anything());
        $this->_jobMock->expects($this->exactly($retryCount))
                       ->method('setStatus')
                       ->with(Mage_Webhook_Model_Dispatch_Job::RETRY);


        for ($c = 0; $c < $retryCount; $c++) {
            $this->_handler->handleFailure($this->_jobMock);
        }
    }

    /**
     * Tests that a job which has failed over 8 times is marked as failed.
     */
    public function testJobFailAfter8Retries()
    {
        $this->_jobMock->expects($this->exactly(1))
                       ->method('getRetryCount')
                       ->will($this->returnValue(8));

        $this->_jobMock->expects($this->exactly(1))
                       ->method('setStatus')
                       ->with(Mage_Webhook_Model_Dispatch_Job::FAILED);

        $this->_handler->handleFailure($this->_jobMock);
    }
}
