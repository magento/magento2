<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Captcha\Model\DefaultModel as CaptchaModel;
use Magento\Captcha\Observer\CheckGuestCheckoutObserver;
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
class CheckGuestCheckoutObserverTest extends \PHPUnit_Framework_TestCase
{
    const FORM_ID = 'guest_checkout';

    /**
     * @var CheckGuestCheckoutObserver
     */
    private $checkGuestCheckoutObserver;

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
        $onepageModelTypeMock = $this->getMock(Onepage::class, [], [], '', false);
        $captchaHelperMock = $this->getMock(CaptchaDataHelper::class, [], [], '', false);
        $this->objectManager = new ObjectManager($this);
        $this->actionFlagMock = $this->getMock(ActionFlag::class, [], [], '', false);
        $this->captchaStringResolverMock = $this->getMock(CaptchaStringResolver::class, [], [], '', false);
        $this->captchaModelMock = $this->getMock(CaptchaModel::class, [], [], '', false);
        $this->quoteModelMock = $this->getMock(Quote::class, [], [], '', false);
        $this->controllerMock = $this->getMock(Action::class, [], [], '', false);
        $this->requestMock = $this->getMock(Http::class, [], [], '', false);
        $this->responseMock = $this->getMock(HttpResponse::class, [], [], '', false);
        $this->observer = new Observer(['controller_action' => $this->controllerMock]);
        $this->jsonHelperMock = $this->getMock(JsonHelper::class, [], [], '', false);

        $this->checkGuestCheckoutObserver = $this->objectManager->getObject(
            CheckGuestCheckoutObserver::class,
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

    public function testCheckGuestCheckoutForRegister()
    {
        $this->quoteModelMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_REGISTER);
        $this->captchaModelMock->expects($this->never())
            ->method('isRequired');

        $this->checkGuestCheckoutObserver->execute($this->observer);
    }

    public function testCheckGuestCheckoutWithNoCaptchaRequired()
    {
        $this->quoteModelMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_GUEST);
        $this->captchaModelMock->expects($this->once())
            ->method('isRequired')
            ->willReturn(false);
        $this->captchaModelMock->expects($this->never())
            ->method('isCorrect');

        $this->checkGuestCheckoutObserver->execute($this->observer);
    }

    public function testCheckGuestCheckoutWithIncorrectCaptcha()
    {
        $captchaValue = 'some_word';
        $encodedJsonValue = '{}';

        $this->quoteModelMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_GUEST);
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

        $this->checkGuestCheckoutObserver->execute($this->observer);
    }

    public function testCheckGuestCheckoutWithCorrectCaptcha()
    {
        $this->quoteModelMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_GUEST);
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

        $this->checkGuestCheckoutObserver->execute($this->observer);
    }
}
