<?php
/**
 * \Magento\Webhook\Model\Job\QueueReader
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

class QueueReaderTest extends \PHPUnit_Framework_TestCase
{

    /** @var \Magento\Webhook\Model\Job\QueueReader */
    private $_jobQueue;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_mockCollection;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_mockIterator;

    protected function setUp()
    {
        $this->_mockCollection = $this->getMockBuilder('Magento\Webhook\Model\Resource\Job\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockIterator = $this->getMockBuilder('ArrayIterator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockCollection->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue($this->_mockIterator));
        $this->_jobQueue = new \Magento\Webhook\Model\Job\QueueReader($this->_mockCollection);
    }

    public function testPollNothing()
    {
        $this->_mockIterator->expects($this->once())
            ->method('valid')
            ->will($this->returnValue(false));
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
}
