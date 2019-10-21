<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Newsletter\Test\Unit\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Newsletter\Model\Config;
use Magento\Newsletter\Observer\PredispatchNewsletterObserver;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Newsletter\Observer\PredispatchNewsletterObserver
 */
class PredispatchNewsletterObserverTest extends TestCase
{
    /**
     * @var Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockObject;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $newsletterConfig;

    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        $this->configMock = $this->createMock(ScopeConfigInterface::class);
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();
        $this->redirectMock = $this->createMock(RedirectInterface::class);
        $this->newsletterConfig = $this->createMock(Config::class);
        $this->objectManager = new ObjectManager($this);
        $this->mockObject = new PredispatchNewsletterObserver(
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
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResponse', 'getData', 'setRedirect'])
            ->getMockForAbstractClass();

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

        $this->assertNull($this->mockObject->execute($observerMock));
    }

    /**
     * Test with disabled newsletter active config.
     */
    public function testNewsletterDisabled() : void
    {
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getControllerAction', 'getResponse'])
            ->getMockForAbstractClass();

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

        $this->assertNull($this->mockObject->execute($observerMock));
    }
}
