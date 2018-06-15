<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Captcha\Model\DefaultModel as CaptchaModel;
use Magento\Captcha\Observer\CheckRegisterCheckoutObserver;
use Magento\Captcha\Helper\Data as CaptchaDataHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckRegisterCheckoutObserverTest extends \PHPUnit\Framework\TestCase
{
    const FORM_ID = 'register_during_checkout';

    /**
     * @var CheckRegisterCheckoutObserver
     */
    private $checkRegisterCheckoutObserver;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Observer
     */
    private $observer;

    /**
     * @var HttpResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var HttpResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFlagMock;

    /**
     * @var CaptchaStringResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $captchaStringResolverMock;

    /**
     * @var JsonHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonHelperMock;

    /**
     * @var CaptchaModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $captchaModelMock;

    /**
     * @var  Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteModelMock;

    /**
     * @var Action|\PHPUnit_Framework_MockObject_MockObject
     */
    private $controllerMock;

    protected function setUp()
    {
        $onepageModelTypeMock = $this->createMock(Onepage::class);
        $captchaHelperMock = $this->createMock(CaptchaDataHelper::class);
        $this->objectManager = new ObjectManager($this);
        $this->actionFlagMock = $this->createMock(ActionFlag::class);
        $this->captchaStringResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->captchaModelMock = $this->createMock(CaptchaModel::class);
        $this->quoteModelMock = $this->createMock(Quote::class);
        $this->controllerMock = $this->createMock(Action::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->responseMock = $this->createMock(HttpResponse::class);
        $this->observer = new Observer(['controller_action' => $this->controllerMock]);
        $this->jsonHelperMock = $this->createMock(JsonHelper::class);

        $this->checkRegisterCheckoutObserver = $this->objectManager->getObject(
            CheckRegisterCheckoutObserver::class,
            [
                'helper' => $captchaHelperMock,
                'actionFlag' => $this->actionFlagMock,
                'captchaStringResolver' => $this->captchaStringResolverMock,
                'typeOnepage' => $onepageModelTypeMock,
                'jsonHelper' => $this->jsonHelperMock
            ]
        );

        $captchaHelperMock->expects($this->once())
            ->method('getCaptcha')
            ->with(self::FORM_ID)
            ->willReturn($this->captchaModelMock);
        $onepageModelTypeMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteModelMock);
    }

    public function testCheckRegisterCheckoutForGuest()
    {
        $this->quoteModelMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_GUEST);
        $this->captchaModelMock->expects($this->never())
            ->method('isRequired');

        $this->checkRegisterCheckoutObserver->execute($this->observer);
    }

    public function testCheckRegisterCheckoutWithNoCaptchaRequired()
    {
        $this->quoteModelMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_REGISTER);
        $this->captchaModelMock->expects($this->once())
            ->method('isRequired')
            ->willReturn(false);
        $this->captchaModelMock->expects($this->never())
            ->method('isCorrect');

        $this->checkRegisterCheckoutObserver->execute($this->observer);
    }

    public function testCheckRegisterCheckoutWithIncorrectCaptcha()
    {
        $captchaValue = 'some_word';
        $encodedJsonValue = '{}';

        $this->quoteModelMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_REGISTER);
        $this->captchaModelMock->expects($this->once())
            ->method('isRequired')
            ->willReturn(true);
        $this->controllerMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->controllerMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->controllerMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($this->requestMock, self::FORM_ID)
            ->willReturn($captchaValue);
        $this->captchaModelMock->expects($this->once())
            ->method('isCorrect')
            ->with($captchaValue)
            ->willReturn(false);
        $this->actionFlagMock->expects($this->once())
            ->method('set')
            ->with('', Action::FLAG_NO_DISPATCH, true);
        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->willReturn($encodedJsonValue);
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with($encodedJsonValue);

        $this->checkRegisterCheckoutObserver->execute($this->observer);
    }

    public function testCheckRegisterCheckoutWithCorrectCaptcha()
    {
        $this->quoteModelMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_REGISTER);
        $this->captchaModelMock->expects($this->once())
            ->method('isRequired')
            ->willReturn(true);
        $this->controllerMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($this->requestMock, self::FORM_ID)
            ->willReturn('some_word');
        $this->captchaModelMock->expects($this->once())
            ->method('isCorrect')
            ->with('some_word')
            ->willReturn(true);
        $this->actionFlagMock->expects($this->never())
            ->method('set');

        $this->checkRegisterCheckoutObserver->execute($this->observer);
    }
}
