<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\DefaultModel;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Captcha\Observer\CheckUserLoginBackendObserver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\Plugin\AuthenticationException;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CheckUserLoginBackendObserverTest
 */
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
    protected function setUp()
    {
        $this->helperMock = $this->createMock(Data::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->captchaStringResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->requestMock = $this->createMock(RequestInterface::class);

        $this->observer = new CheckUserLoginBackendObserver(
            $this->helperMock,
            $this->captchaStringResolverMock,
            $this->requestMock
        );
    }

    /**
     * Test check user login in backend with correct captcha
     *
     * @dataProvider captchaCorrectnessCheckDataProvider
     * @param bool $isRequired
     * @param bool $isCorrect
     * @param int $invokedTimes
     * @return void
     * @throws AuthenticationException
     */
    public function testCheckOnBackendLoginWithCorrectCaptcha($isRequired, $isCorrect, $invokedTimes)
    {
        $formId = 'backend_login';
        $login = 'admin';
        $captchaValue = 'captcha-value';

        $observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $eventMock = $this->createPartialMock(Event::class, ['getUsername']);
        $captcha = $this->createMock(DefaultModel::class);

        $eventMock->expects($this->any())
            ->method('getUsername')
            ->willReturn('admin');
        $observerMock->expects($this->any())
            ->method('getEvent')
            ->willReturn($eventMock);
        $captcha->expects($this->once())->method('isRequired')
            ->with($login)
            ->willReturn($isRequired);
        $captcha->expects($this->exactly($invokedTimes))
            ->method('isCorrect')
            ->with($captchaValue)
            ->willReturn($isCorrect);
        $this->helperMock->expects($this->once())
            ->method('getCaptcha')
            ->with($formId)
            ->willReturn($captcha);

        $this->captchaStringResolverMock->expects($this->exactly($invokedTimes))
            ->method('resolve')
            ->with($this->requestMock, $formId)
            ->willReturn($captchaValue);

        $this->messageManagerMock->expects($this->exactly(0))
            ->method('addError')
            ->with(__('Incorrect CAPTCHA'));

        $this->observer->execute($observerMock);
    }

    /**
     * @return array
     */
    public function captchaCorrectnessCheckDataProvider()
    {
        return [
            [true, true, 1],
            [false, true, 0]
        ];
    }


    /**
     * Test check user login in backend with wrong captcha
     *
     * @return void
     * @throws AuthenticationException
     */
    public function testCheckOnBackendLoginWithWrongCaptcha()
    {
        $formId = 'backend_login';
        $login = 'admin';
        $captchaValue = 'captcha-value';

        $observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $eventMock = $this->createPartialMock(Event::class, ['getUsername']);
        $captcha = $this->createMock(DefaultModel::class);

        $eventMock->expects($this->any())
            ->method('getUsername')
            ->willReturn('admin');
        $observerMock->expects($this->any())
            ->method('getEvent')
            ->willReturn($eventMock);
        $captcha->expects($this->once())->method('isRequired')
            ->with($login)
            ->willReturn(true);
        $captcha->expects($this->exactly(1))
            ->method('isCorrect')
            ->with($captchaValue)
            ->willReturn(false);
        $this->helperMock->expects($this->once())
            ->method('getCaptcha')
            ->with($formId)
            ->willReturn($captcha);

        $this->captchaStringResolverMock->expects($this->exactly(1))
            ->method('resolve')
            ->with($this->requestMock, $formId)
            ->willReturn($captchaValue);

        $this->expectException(AuthenticationException::class, 'Incorrect CAPTCHA.');

        $this->observer->execute($observerMock);
    }
}
