<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\App\FrontController;

use Magento\Customer\Model\App\FrontController\DeleteCookieWhenCustomerNotExistPlugin;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Customer\Model\Session;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\Customer\Model\App\FrontController\DeleteCookieWhenCustomerNotExistPluginTest.
 */
class DeleteCookieWhenCustomerNotExistPluginTest extends TestCase
{
    /**
     * @var DeleteCookieWhenCustomerNotExistPlugin
     */
    protected DeleteCookieWhenCustomerNotExistPlugin $plugin;

    /**
     * @var ResponseHttp|MockObject
     */
    protected ResponseHttp|MockObject $responseHttpMock;

    /**
     * @var Session|MockObject
     */
    protected MockObject|Session $customerSessionMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->responseHttpMock = $this->createMock(ResponseHttp::class);
        $this->plugin = new DeleteCookieWhenCustomerNotExistPlugin(
            $this->responseHttpMock,
            $this->customerSessionMock
        );
    }

    public function testBeforeDispatch()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(0);
        $this->plugin->beforeDispatch();
    }
}
