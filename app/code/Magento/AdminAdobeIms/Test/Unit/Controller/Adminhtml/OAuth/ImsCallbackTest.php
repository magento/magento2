<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Controller\Adminhtml\OAuth;

use Magento\AdminAdobeIms\Controller\Adminhtml\OAuth\ImsCallback;
use Magento\AdminAdobeIms\Logger\AdminAdobeImsLogger;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Service\AdminLoginProcessService;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdminAdobeIms\Service\ImsOrganizationService;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\Validator\Locale;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\AdminAdobeIms\Controller\Adminhtml\OAuth\ImsCallback controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImsCallbackTest extends TestCase
{
    /**
     * @var Validator|mixed|MockObject
     */
    private mixed $validatorMock;

    /**
     * @var RequestInterface|mixed|MockObject
     */
    private mixed $requestMock;

    /**
     * @var AdminAdobeImsLogger|mixed|MockObject
     */
    private mixed $loggerMock;

    /**
     * @var Data|mixed|MockObject
     */
    private mixed $helperMock;

    /**
     * @var Manager|mixed|MockObject
     */
    private mixed $messagesMock;

    /**
     * @var mixed|ImsCallback
     */
    private mixed $controller;

    /**
     * @var Session|mixed|MockObject
     */
    private mixed $authSessionMock;

    /**
     * @var ActionFlag|mixed|MockObject
     */
    private mixed $actionFlagMock;

    /**
     * @var Session|mixed|MockObject
     */
    private mixed $authMock;

    /**
     * @var ObjectManager|mixed|MockObject
     */
    private mixed $objectManagerMock;

    /**
     * @var Locale|mixed|MockObject
     */
    private mixed $localeMock;

    /**
     * @var \Magento\Backend\Model\Locale\Manager|mixed|MockObject
     */
    private mixed $managerMock;

    /**
     * @var ImsConnection|mixed|MockObject
     */
    private $imsConnectionMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'create'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParam', 'setParam'])
            ->getMock();
        $responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();
        $this->validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(AdminAdobeImsLogger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messagesMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addComplexErrorMessage'])
            ->getMockForAbstractClass();
        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['setIsUrlNotice', 'getLocale'])
            ->getMock();
        $this->authMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imsConnectionMock = $this->getMockBuilder(ImsConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeMock = $this->getMockBuilder(Locale::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isValid'])
            ->getMock();
        $this->managerMock = $this->getMockBuilder(\Magento\Backend\Model\Locale\Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['switchBackendInterfaceLocale'])
            ->getMock();
        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHomePageUrl', 'getUrl'])
            ->getMock();
        $imsConfigMock = $this->getMockBuilder(ImsConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imsOrganizationServiceMock = $this->getMockBuilder(ImsOrganizationService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loginProcessServiceMock = $this->createMock(AdminLoginProcessService::class);
        $contextMock = $this->getMockBuilder(Context::class)
            ->addMethods(['getFrontController', 'getTranslator'])
            ->onlyMethods([
                'getRequest',
                'getFormKeyValidator',
                'getMessageManager',
                'getHelper',
                'getActionFlag',
                'getResponse',
                'getSession',
                'getAuth',
                'getObjectManager'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $contextMock->expects($this->once())->method('getResponse')->willReturn($responseMock);
        $contextMock->expects($this->once())->method('getAuth')->willReturn($this->authMock);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->once())->method('getFormKeyValidator')->willReturn($this->validatorMock);
        $contextMock->expects($this->once())->method('getActionFlag')->willReturn($this->actionFlagMock);
        $contextMock->expects($this->once())->method('getSession')->willReturn($this->authSessionMock);
        $contextMock->expects($this->once())->method('getHelper')->willReturn($this->helperMock);
        $contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messagesMock);

        $this->controller = new ImsCallback(
            $contextMock,
            $this->imsConnectionMock,
            $imsConfigMock,
            $imsOrganizationServiceMock,
            $loginProcessServiceMock,
            $this->loggerMock,
        );
    }

    /**
     * Validate if state exists in ims callback url.
     * @return void
     */
    public function testStateExistsInImsCallback(): void
    {
        $this->addMockData();
        $this->validatorMock->expects($this->once())->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        $response = $this->controller->dispatch($this->requestMock);
        $this->assertEquals(200, $response->getHttpResponseCode());
    }

    /**
     * Validate if state not exists in ims callback url.
     * @return void
     */
    public function testStateNotExistsInImsCallback(): void
    {
        $this->addMockData();
        $this->validatorMock->expects($this->once())->method('validate')
            ->with($this->requestMock)
            ->willReturn(false);
        $response = $this->controller->dispatch($this->requestMock);
        $this->assertEquals(302, $response->getHttpResponseCode());
    }

    /**
     * Add mock data for tests
     * @return void
     */
    private function addMockData(): void
    {
        $this->requestMock->expects($this->any())->method('setParam')
            ->with('form_key')
            ->willReturnSelf();
        $this->requestMock->expects($this->any())->method('getParam')
            ->withConsecutive(['state'], ['locale'])
            ->willReturnOnConsecutiveCalls('abc', 'en');
        $this->authSessionMock->expects($this->any())->method('setIsUrlNotice')
            ->willReturnSelf();
        $this->authSessionMock->expects($this->any())->method('getLocale')
            ->willReturn('en');
        $this->actionFlagMock->expects($this->any())->method('get')
            ->with('', 'check_url_settings')
            ->willReturn(true);
        $this->helperMock->expects($this->any())->method('getHomePageUrl')
            ->willReturn('https://magento.test/admin');
        $this->helperMock->expects($this->any())->method('getUrl')
            ->willReturn('https://magento.test/admin');
        $this->authMock->expects($this->any())->method('isLoggedIn')->willReturn(false);
        $this->objectManagerMock
            ->method('get')
            ->withConsecutive([Locale::class], [\Magento\Backend\Model\Locale\Manager::class])
            ->willReturnOnConsecutiveCalls($this->localeMock, $this->managerMock);
    }
}
