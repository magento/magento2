<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Log\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Log\Model\Log
     */
    protected $log;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    protected function setUp()
    {
        $this->registry = $this->getMock('Magento\Framework\Registry');
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $resource = $this->getMockBuilder('\Magento\Log\Model\Resource\Log')
            ->setMethods(['clean', 'getIdFieldName'])
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('visitor_id'));
        $resource->expects($this->any())->method('clean')->will($this->returnSelf());

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = $this->objectManagerHelper->getConstructArguments(
            'Magento\Log\Model\Log',
            [
                'registry' => $this->registry,
                'scopeConfig' => $this->scopeConfig,
                'resource' => $resource
            ]
        );
        $this->log = $this->objectManagerHelper->getObject('Magento\Log\Model\Log', $arguments);
    }

    public function testGetLogCleanTime()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(Log::XML_LOG_CLEAN_DAYS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(30));
        $this->assertEquals(2592000, $this->log->getLogCleanTime());
    }

    public function testClean()
    {
        $this->assertSame($this->log, $this->log->clean());
    }

    public function testGetOnlineMinutesInterval()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(
                'customer/online_customers/online_minutes_interval',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->will($this->returnValue(10));

        $this->assertEquals(10, $this->log->getOnlineMinutesInterval());
    }
}
