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
class ContextPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\App\Action\ContextPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Framework\App\Http\Context $httpContext|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $httpContextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->httpContextMock = $this->createMock(\Magento\Framework\App\Http\Context::class);
        $this->subjectMock = $this->createMock(\Magento\Framework\App\Action\Action::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
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
            ->willReturn(1);
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->httpContextMock->expects($this->atLeastOnce())
            ->method('setValue')
            ->willReturnMap(
                
                    [
                        [Context::CONTEXT_GROUP, 'UAH', $this->httpContextMock],
                        [Context::CONTEXT_AUTH, 0, $this->httpContextMock],
                    ]
                
            );
        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
