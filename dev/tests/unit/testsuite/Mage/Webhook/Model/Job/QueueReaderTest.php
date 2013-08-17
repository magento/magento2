<?php
/**
 * Mage_Webhook_Model_Job_QueueReader
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
class Mage_Webhook_Model_Job_QueueReaderTest extends PHPUnit_Framework_TestCase
{

    /** @var Mage_Webhook_Model_Job_QueueReader */
    private $_jobQueue;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $_mockCollection;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $_mockIterator;

    public function setUp()
    {
        $this->_mockCollection = $this->getMockBuilder('Mage_Webhook_Model_Resource_Job_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockCollection->expects($this->once())
            ->method('setPageSize')
            ->with($this->equalTo(Mage_Webhook_Model_Job_QueueReader::PAGE_SIZE))
            ->will($this->returnSelf());
        $this->_mockCollection->expects($this->once())
            ->method('setOrder')
            ->with($this->equalTo('created_at'), $this->equalTo(Varien_Data_Collection::SORT_ORDER_DESC));
        $this->_mockCollection->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->will($this->returnSelf());
        $this->_mockIterator = $this->getMockBuilder('ArrayIterator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockCollection->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue($this->_mockIterator));
        $this->_jobQueue = new Mage_Webhook_Model_Job_QueueReader($this->_mockCollection);
    }

    public function testPollNothing()
    {
        $this->_mockIterator->expects($this->once())
            ->method('valid')
            ->will($this->returnValue(false));
        $this->_mockCollection->expects($this->once())
            ->method('getCurPage')
            ->will($this->returnValue(PHP_INT_MAX));
        $this->_mockCollection->expects($this->once())
            ->method('getLastPageNumber')
            ->will($this->returnValue(~PHP_INT_MAX));
        $this->assertNull($this->_jobQueue->poll());
    }

    public function testPollIteratorJob()
    {
        $this->_mockIterator->expects($this->once())
            ->method('valid')
            ->will($this->returnValue(true));

        $job = 'TEST_JOB';
        $this->_mockIterator->expects($this->once())
            ->method('current')
            ->will($this->returnValue($job));

        $this->_mockIterator->expects($this->once())
            ->method('next');

        $this->assertSame($job, $this->_jobQueue->poll());
    }

    public function testPollNextPageJob()
    {
        $this->_mockIterator->expects($this->once())
            ->method('valid')
            ->will($this->returnValue(false));
        $this->_mockCollection->expects($this->exactly(2))
            ->method('getCurPage')
            ->will($this->returnValue(1));
        $this->_mockCollection->expects($this->once())
            ->method('getLastPageNumber')
            ->will($this->returnValue(PHP_INT_MAX));

        $this->_mockCollection->expects($this->once())
            ->method('setCurPage')
            ->with($this->equalTo(2));

        $this->_mockCollection->expects($this->once())
            ->method('setPageLimit')
            ->will($this->returnSelf());
        $this->_mockCollection->expects($this->once())
            ->method('clear');

        $job = 'TEST_JOB';
        $this->_mockIterator->expects($this->once())
            ->method('current')
            ->will($this->returnValue($job));

        $this->_mockIterator->expects($this->once())
            ->method('next');

        $this->assertEquals($job, $this->_jobQueue->poll());
    }
}