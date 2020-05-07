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
use Magento\Captcha\Observer\CheckUserCreateObserver;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckUserCreateObserverTest extends TestCase
{
    /**
     * @var CheckUserCreateObserver
     */
    protected $checkUserCreateObserver;

    /**
     * @var MockObject
     */
    protected $_helper;

    /**
     * @var MockObject
     */
    protected $_actionFlag;

    /**
     * @var MockObject
     */
    protected $_messageManager;

    /**
     * @var MockObject
     */
    protected $_session;

    /**
     * @var MockObject
     */
    protected $_urlManager;

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var CaptchaStringResolver|MockObject
     */
    protected $captchaStringResolver;

    /**
     * @var MockObject
     */
    protected $_captcha;

    /**
     * @var MockObject
     */
    protected $redirect;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_helper = $this->createMock(Data::class);
        $this->_actionFlag = $this->createMock(ActionFlag::class);
        $this->_messageManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->_session = $this->createMock(SessionManager::class);
        $this->_urlManager = $this->createMock(Url::class);
        $this->captchaStringResolver = $this->createMock(CaptchaStringResolver::class);
        $this->redirect = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->checkUserCreateObserver = $this->_objectManager->getObject(
            CheckUserCreateObserver::class,
            [
                'helper' => $this->_helper,
                'actionFlag' => $this->_actionFlag,
                'messageManager' => $this->_messageManager,
                'session' => $this->_session,
                'urlManager' => $this->_urlManager,
                'redirect' => $this->redirect,
                'captchaStringResolver' => $this->captchaStringResolver
            ]
        );
        $this->_captcha = $this->createMock(DefaultModel::class);
    }

    public function testCheckUserCreateRedirectsError()
    {
        $formId = 'user_create';
        $captchaValue = 'some-value';
        $warningMessage = 'Incorrect CAPTCHA';
        $redirectRoutePath = '*/*/create';
        $redirectUrl = 'http://magento.com/customer/account/create/';

        $request = $this->createMock(Http::class);

        $this->redirect->expects(
            $this->once()
        )->method(
            'error'
        )->with(
            $redirectUrl
        )->willReturn(
            $redirectUrl
        );

        $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $response->expects($this->once())->method('setRedirect')->with($redirectUrl);

        $this->_urlManager->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            $redirectRoutePath,
            ['_nosecret' => true]
        )->willReturn(
            $redirectUrl
        );

        $controller = $this->createMock(Action::class);
        $controller->expects($this->any())->method('getRequest')->willReturn($request);
        $controller->expects($this->any())->method('getResponse')->willReturn($response);
        $this->_captcha->expects($this->any())->method('isRequired')->willReturn(true);
        $this->_captcha->expects(
            $this->once()
        )->method(
            'isCorrect'
        )->with(
            $captchaValue
        )->willReturn(
            false
        );
        $this->captchaStringResolver->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            $request,
            $formId
        )->willReturn(
            $captchaValue
        );
        $this->_helper->expects(
            $this->any()
        )->method(
            'getCaptcha'
        )->with(
            $formId
        )->willReturn(
            $this->_captcha
        );
        $this->_messageManager->expects($this->once())->method('addErrorMessage')->with($warningMessage);
        $this->_actionFlag->expects(
            $this->once()
        )->method(
            'set'
        )->with(
            '',
            Action::FLAG_NO_DISPATCH,
            true
        );

        $this->checkUserCreateObserver->execute(
            new Observer(['controller_action' => $controller])
        );
    }
}
