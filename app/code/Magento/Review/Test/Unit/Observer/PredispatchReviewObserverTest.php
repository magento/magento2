<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Review\Test\Unit\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Review\Observer\PredispatchReviewObserver;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Review\Observer\PredispatchReviewObserver
 */
class PredispatchReviewObserverTest extends TestCase
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
     * @inheritdoc
     */
    protected function setUp() : void
    {
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(['setRedirect'])
                                   ->getMockForAbstractClass();
        $this->redirectMock = $this->getMockBuilder(RedirectInterface::class)
                                   ->getMock();
        $this->objectManager = new ObjectManager($this);
        $this->mockObject = $this->objectManager->getObject(
            PredispatchReviewObserver::class,
            [
                'scopeConfig' => $this->configMock,
                'url' => $this->urlMock
            ]
        );
    }

    /**
     * Test with enabled review active config.
     */
    public function testReviewEnabled() : void
    {
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResponse', 'getData', 'setRedirect'])
            ->getMockForAbstractClass();

        $this->configMock->method('getValue')
            ->with(PredispatchReviewObserver::XML_PATH_REVIEW_ACTIVE, ScopeInterface::SCOPE_STORE)
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
     * Test with disabled review active config.
     */
    public function testReviewDisabled() : void
    {
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getControllerAction', 'getResponse'])
            ->getMockForAbstractClass();

        $this->configMock->expects($this->at(0))
            ->method('getValue')
            ->with(PredispatchReviewObserver::XML_PATH_REVIEW_ACTIVE, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $expectedRedirectUrl = 'https://test.com/index';

        $this->configMock->expects($this->at(1))
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
