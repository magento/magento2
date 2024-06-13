<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types = 1);

namespace Magento\Newsletter\Test\Unit\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Newsletter\Model\Config;
use Magento\Newsletter\Observer\PredispatchNewsletterObserver;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Newsletter\Observer\PredispatchNewsletterObserver
 */
class PredispatchNewsletterObserverTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var PredispatchNewsletterObserver
     */
    private $mockObjectMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirectMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Config|MockObject
     */
    private $newsletterConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ScopeConfigInterface::class);
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->responseMock = $this->createPartialMockWithReflection(
            ResponseInterface::class,
            ['sendResponse', 'setRedirect']
        );
        $this->redirectMock = $this->createMock(RedirectInterface::class);
        $this->newsletterConfig = $this->createMock(Config::class);
        $this->objectManager = new ObjectManager($this);
        $this->mockObjectMock = new PredispatchNewsletterObserver(
            $this->configMock,
            $this->urlMock,
            $this->newsletterConfig
        );
    }

    /**
     * Test with enabled newsletter active config.
     */
    public function testNewsletterEnabled() : void
    {
        $observerMock = $this->createPartialMockWithReflection(
            Observer::class,
            ['getData', 'getResponse', 'setRedirect']
        );

        $this->newsletterConfig->expects($this->once())
            ->method('isActive')
            ->with(ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $observerMock->expects($this->never())
            ->method('getData')
            ->with('controller_action')
            ->willReturnSelf();

        $observerMock->expects($this->never())
            ->method('getResponse')
            ->willReturnSelf();

        $this->assertNull($this->mockObjectMock->execute($observerMock));
    }

    /**
     * Test with disabled newsletter active config.
     */
    public function testNewsletterDisabled() : void
    {
        $observerMock = $this->createPartialMockWithReflection(
            Observer::class,
            ['getControllerAction', 'getResponse']
        );

        $this->newsletterConfig->expects($this->once())
            ->method('isActive')
            ->with(ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $expectedRedirectUrl = 'https://test.com/index';
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('web/default/no_route', ScopeInterface::SCOPE_STORE)
            ->willReturn($expectedRedirectUrl);

        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($expectedRedirectUrl);

        $observerMock->expects($this->once())
            ->method('getControllerAction')
            ->willReturnSelf();

        $observerMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('setRedirect')
            ->with($expectedRedirectUrl);

        $this->assertNull($this->mockObjectMock->execute($observerMock));
    }
}
