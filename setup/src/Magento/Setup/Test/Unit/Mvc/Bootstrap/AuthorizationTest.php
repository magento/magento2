<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Mvc\Bootstrap;

class AuthorizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    /**
     * @var \Magento\Backend\Model\Session\AdminConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionAdminConfigMock;

    /**
     * @var \Magento\Backend\Model\Auth\SessionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authSessionFactoryMock;

    /**
     * @var \Magento\Backend\Model\Auth|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authMock;

    /**
     * @var \Magento\Backend\Model\UrlFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlFactoryMock;

    /**
     * @var \Magento\Backend\App\BackendAppList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backendAppListMock;

    /**
     * @var \Magento\Backend\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var \Magento\Backend\App\BackendApp|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backendAppMock;

    /**
     * @var \Magento\Backend\Model\Auth\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authSessionMock;

    /**
     * @var \Magento\Setup\Mvc\Bootstrap\Authorization
     */
    private $authorization;

    protected function setUp()
    {
        $this->appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionAdminConfigMock = $this->getMockBuilder(\Magento\Backend\Model\Session\AdminConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authSessionFactoryMock = $this->getMockBuilder(\Magento\Backend\Model\Auth\SessionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->authMock = $this->getMockBuilder(\Magento\Backend\Model\Auth::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlFactoryMock = $this->getMockBuilder(\Magento\Backend\Model\UrlFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendAppListMock = $this->getMockBuilder(\Magento\Backend\App\BackendAppList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlMock = $this->getMockBuilder(\Magento\Backend\Model\Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendAppMock = $this->getMockBuilder(\Magento\Backend\App\BackendApp::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authSessionMock = $this->getMockBuilder(\Magento\Backend\Model\Auth\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorization = new \Magento\Setup\Mvc\Bootstrap\Authorization(
            $this->appStateMock,
            $this->sessionAdminConfigMock,
            $this->authSessionFactoryMock,
            $this->authMock,
            $this->urlFactoryMock,
            $this->backendAppListMock
        );
    }

    /**
     * @param int $isLoggedInCallCount
     * @param boolean $isLoggedInReturn
     * @param int $isAllowedCallCount
     * @param boolean $isAllowedReturn
     * @param int $destroyCallCount
     * @param boolean $expected
     * @dataProvider authorizeDataProvider
     */
    public function testAuthorize(
        $isLoggedInCallCount,
        $isLoggedInReturn,
        $isAllowedCallCount,
        $isAllowedReturn,
        $destroyCallCount,
        $expected
    ) {
        $baseUrl = 'base url';
        $cookiePath = 'cookie path';
        $this->appStateMock->expects($this->once())
            ->method('setAreaCode')
            ->with(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->backendAppListMock->expects($this->once())
            ->method('getBackendApp')
            ->with('setup')
            ->willReturn($this->backendAppMock);
        $this->urlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->urlMock);
        $this->urlMock->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);
        $this->backendAppMock->expects($this->once())
            ->method('getCookiePath')
            ->willReturn($cookiePath);
        $this->sessionAdminConfigMock->expects($this->once())
            ->method('setCookiePath')
            ->with($baseUrl . $cookiePath);
        $this->authSessionFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'sessionConfig' => $this->sessionAdminConfigMock,
                    'appState' => $this->appStateMock
                ]
            )
            ->willReturn($this->authSessionMock);
        $this->authMock->expects($this->exactly($isLoggedInCallCount))
            ->method('isLoggedIn')
            ->willReturn($isLoggedInReturn);
        $this->authSessionMock->expects($this->exactly($isAllowedCallCount))
            ->method('isAllowed')
            ->with('Magento_Backend::setup_wizard')
            ->willReturn($isAllowedReturn);
        $this->authSessionMock->expects($this->exactly($destroyCallCount))
            ->method('destroy');
        $this->assertEquals($expected, $this->authorization->authorize());
    }

    /**
     * @return array
     */
    public function authorizeDataProvider()
    {
        return [
            [
                1,
                false,
                0,
                null,
                1,
                false
            ],
            [
                1,
                true,
                1,
                false,
                1,
                false
            ],
            [
                1,
                true,
                1,
                true,
                0,
                true
            ]
        ];
    }
}
