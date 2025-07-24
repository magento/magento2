<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\App\Action;

use Magento\Customer\Model\App\Action\ContextPlugin;
use Magento\Customer\Model\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Http\Context as HttpContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests to cover ContextPlugin for Action Context
 */
class ContextPluginTest extends TestCase
{
    public const STUB_CUSTOMER_GROUP = 'UAH';
    public const STUB_CUSTOMER_NOT_LOGGED_IN = 0;
    /**
     * @var ContextPlugin
     */
    protected $plugin;

    /**
     * @var Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var HttpContext|MockObject
     */
    protected $httpContextMock;

    /**
     * @var Action|MockObject
     */
    protected $subjectMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->httpContextMock = $this->createMock(HttpContext::class);
        $this->subjectMock = $this->createMock(Action::class);
        $this->plugin = new ContextPlugin(
            $this->customerSessionMock,
            $this->httpContextMock
        );
    }

    public function testBeforeExecute()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->httpContextMock->expects($this->atLeastOnce())
            ->method('setValue')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) use (&$callCount) {
                $callCount++;
                switch ($callCount) {
                    case 1:
                        if ($arg1 === Context::CONTEXT_GROUP && $arg3 === 0) {
                             return $this->httpContextMock;
                        }
                        break;
                    case 2:
                        if ($arg1 === Context::CONTEXT_AUTH && $arg2 === true &&
                            $arg3 === self::STUB_CUSTOMER_NOT_LOGGED_IN) {
                             return $this->httpContextMock;
                        }
                        break;
                }
            });
        $this->plugin->beforeExecute($this->subjectMock);
    }
}
