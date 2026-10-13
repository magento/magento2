<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types = 1);

namespace Magento\Review\Test\Unit\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\UrlInterface;
use Magento\Review\Observer\PredispatchReviewObserver;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Review\Observer\PredispatchReviewObserver
 */
class PredispatchReviewObserverTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Observer|MockObject
     */
    private $mockObject;

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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ScopeConfigInterface::class);
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->responseMock = $this->createPartialMock(ResponseHttp::class, ['setRedirect']);
        $this->redirectMock = $this->createMock(RedirectInterface::class);
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
     *
     * @return void
     */
    public function testReviewEnabled() : void
    {
        $observerMock = $this->createPartialMockWithReflection(
            Observer::class,
            ['getData', 'getResponse']
        );

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
     *
     * @return void
     */
    public function testReviewDisabled() : void
    {
        $observerMock = $this->createPartialMockWithReflection(
            Observer::class,
            ['getControllerAction', 'getResponse']
        );

        $expectedRedirectUrl = 'https://test.com/index';

        $this->configMock
            ->method('getValue')
            ->willReturnCallback(function ($arg1, $arg2) use ($expectedRedirectUrl) {
                if ($arg1 == PredispatchReviewObserver::XML_PATH_REVIEW_ACTIVE &&
                    $arg2 == ScopeInterface::SCOPE_STORE) {
                    return false;
                } elseif ($arg1 == 'web/default/no_route' && $arg2 == ScopeInterface::SCOPE_STORE) {
                    return $expectedRedirectUrl;
                }
            });
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
