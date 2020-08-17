<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\DefaultModel;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Captcha\Observer\CheckUserLoginBackendObserver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckUserLoginBackendObserverTest extends TestCase
{
    /**
     * @var CheckUserLoginBackendObserver
     */
    private $observer;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var CaptchaStringResolver|MockObject
     */
    private $captchaStringResolverMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Data|MockObject
     */
    private $helperMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(Data::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->captchaStringResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $this->observer = new CheckUserLoginBackendObserver(
            $this->helperMock,
            $this->captchaStringResolverMock,
            $this->requestMock
        );
    }

    /**
     * Test check user login in backend with correct captcha
     *
     * @dataProvider requiredCaptchaDataProvider
     * @param bool $isRequired
     * @return void
     */
    public function testCheckOnBackendLoginWithCorrectCaptcha(bool $isRequired): void
    {
        $formId = 'backend_login';
        $login = 'admin';
        $captchaValue = 'captcha-value';

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getUsername'])
            ->disableOriginalConstructor()
            ->getMock();
        $captcha = $this->createMock(DefaultModel::class);

        $eventMock->method('getUsername')->willReturn('admin');
        $observerMock->method('getEvent')->willReturn($eventMock);
        $captcha->method('isRequired')->with($login)->willReturn($isRequired);
        $captcha->method('isCorrect')->with($captchaValue)->willReturn(true);
        $this->helperMock->method('getCaptcha')->with($formId)->willReturn($captcha);
        $this->captchaStringResolverMock->method('resolve')->with($this->requestMock, $formId)
            ->willReturn($captchaValue);

        $this->observer->execute($observerMock);
    }

    /**
     * @return array
     */
    public function requiredCaptchaDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Test check user login in backend with wrong captcha
     *
     * @return void
     */
    public function testCheckOnBackendLoginWithWrongCaptcha(): void
    {
        $this->expectException('Magento\Framework\Exception\Plugin\AuthenticationException');
        $formId = 'backend_login';
        $login = 'admin';
        $captchaValue = 'captcha-value';

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getUsername'])
            ->disableOriginalConstructor()
            ->getMock();
        $captcha = $this->createMock(DefaultModel::class);

        $eventMock->method('getUsername')->willReturn($login);
        $observerMock->method('getEvent')->willReturn($eventMock);
        $captcha->method('isRequired')->with($login)->willReturn(true);
        $captcha->method('isCorrect')->with($captchaValue)->willReturn(false);
        $this->helperMock->method('getCaptcha')->with($formId)->willReturn($captcha);
        $this->captchaStringResolverMock->method('resolve')->with($this->requestMock, $formId)
            ->willReturn($captchaValue);

        $this->observer->execute($observerMock);
    }
}
