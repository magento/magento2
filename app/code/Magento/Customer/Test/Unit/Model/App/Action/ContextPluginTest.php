<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\App\Action;

use Magento\Customer\Model\Context;

/**
 * Class ContextPluginTest
 */
class ContextPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\App\Action\ContextPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Framework\App\Http\Context $httpContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->customerSessionMock = $this->getMock(
            \Magento\Customer\Model\Session::class,
            [],
            [],
            '',
            false
        );
        $this->httpContextMock = $this->getMock(
            \Magento\Framework\App\Http\Context::class,
            [],
            [],
            '',
            false
        );
        $this->subjectMock = $this->getMock(\Magento\Framework\App\Action\Action::class, [], [], '', false);
        $this->requestMock = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $this->plugin = new \Magento\Customer\Model\App\Action\ContextPlugin(
            $this->customerSessionMock,
            $this->httpContextMock
        );
    }

    /**
     * Test aroundDispatch
     */
    public function testBeforeDispatch()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue(1));
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $this->httpContextMock->expects($this->atLeastOnce())
            ->method('setValue')
            ->will(
                $this->returnValueMap(
                    [
                        [Context::CONTEXT_GROUP, 'UAH', $this->httpContextMock],
                        [Context::CONTEXT_AUTH, 0, $this->httpContextMock],
                    ]
                )
            );
        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
