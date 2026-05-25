<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Action\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\App\Action\Plugin\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Unit\App\Action\Plugin\_files\ContextTestSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/_files/ContextTestSession.php';

class ContextTest extends TestCase
{
    /** @var ContextTestSession|MockObject */
    private $session;

    /** @var HttpContext|MockObject */
    private $httpContext;

    /** @var StoreManagerInterface|MockObject */
    private $storeManager;

    /** @var StoreCookieManagerInterface|MockObject */
    private $storeCookieManager;

    /** @var RequestInterface|MockObject */
    private $request;

    /** @var Context */
    private $plugin;

    protected function setUp(): void
    {
        $this->session = $this->createMock(ContextTestSession::class);
        $this->httpContext = $this->createMock(HttpContext::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->storeCookieManager = $this->createMock(StoreCookieManagerInterface::class);
        $this->request = $this->createMock(RequestInterface::class);

        $this->plugin = new Context(
            $this->session,
            $this->httpContext,
            $this->storeManager,
            $this->storeCookieManager,
            $this->request
        );
    }

    /**
     * Ensures the plugin fires on ActionInterface controllers (the regression
     * point from #40747), not only AbstractAction descendants.
     */
    public function testBeforeExecuteSetsStoreAndCurrencyOnActionInterface()
    {
        $subject = $this->createMock(ActionInterface::class);

        $this->httpContext->method('getValue')->willReturn(null);
        $this->storeCookieManager->method('getStoreCodeFromCookie')->willReturn('default');
        $this->request->method('getParam')->willReturn('default');
        $this->session->method('getCurrencyCode')->willReturn('USD');

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'isUseStoreInUrl', 'getDefaultCurrencyCode'])
            ->getMock();
        $store->method('getCode')->willReturn('default');
        $store->method('isUseStoreInUrl')->willReturn(true);
        $store->method('getDefaultCurrencyCode')->willReturn('USD');

        $this->storeManager->method('getStore')->willReturn($store);

        $this->httpContext->expects($this->exactly(2))->method('setValue');

        $this->plugin->beforeExecute($subject);
    }

    public function testBeforeExecuteSkipsWhenStoreAlreadySet()
    {
        $subject = $this->createMock(ActionInterface::class);
        $this->httpContext->method('getValue')->willReturn('default');
        $this->httpContext->expects($this->never())->method('setValue');

        $this->plugin->beforeExecute($subject);
    }
}
