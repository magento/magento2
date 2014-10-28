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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Log\Model;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
        $resource = $this->getMockBuilder('Magento\Customer\Model\Resource\Visitor')
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
