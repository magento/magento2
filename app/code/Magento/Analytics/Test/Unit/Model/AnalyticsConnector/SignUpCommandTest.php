<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\AnalyticsConnector;

use Magento\Analytics\Model\AnalyticsConnector\SignUpCommand;
use Magento\Analytics\Model\AnalyticsConnector\SignUpRequest;
use Magento\Analytics\Model\TokenProvider;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\IntegrationManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SignUpCommandTest
 */
class SignUpCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SignUpCommand
     */
    private $signUpCommand;

    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var IntegrationManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $integrationManagerMock;

    /**
     * @var TokenProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenProviderMock;

    /**
     * @var SignUpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $signUpRequestMock;

    protected function setUp()
    {
        $this->analyticsTokenMock =  $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->integrationManagerMock = $this->getMockBuilder(IntegrationManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenProviderMock = $this->getMockBuilder(TokenProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->signUpRequestMock = $this->getMockBuilder(SignUpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->signUpCommand = $objectManagerHelper->getObject(
            SignUpCommand::class,
            [
                'analyticsToken' => $this->analyticsTokenMock,
                'integrationManager' => $this->integrationManagerMock,
                'tokenProvider' => $this->tokenProviderMock,
                'signUpRequest' => $this->signUpRequestMock
            ]
        );
    }

    public function testExecuteSuccess()
    {
        $this->tokenProviderMock->expects($this->once())
            ->method('getToken')
            ->willReturn('IntegrationToken');
        $this->integrationManagerMock->expects($this->once())
            ->method('activateIntegration')
            ->willReturn(true);
        $this->signUpRequestMock->expects($this->once())
            ->method('call')
            ->with('IntegrationToken')
            ->willReturn('MAToken');
        $this->analyticsTokenMock->expects($this->once())
            ->method('storeToken')
            ->with('MAToken')
            ->willReturn(true);
        $this->assertTrue($this->signUpCommand->execute());
    }

    public function testExecuteFailureCannotGenerateToken()
    {
        $this->tokenProviderMock->expects($this->once())
            ->method('getToken')
            ->willReturn(false);
        $this->integrationManagerMock->expects($this->never())
            ->method('activateIntegration')
            ->willReturn(true);
        $this->signUpRequestMock->expects($this->never())
            ->method('call')
            ->willReturn('MAToken');
        $this->analyticsTokenMock->expects($this->never())
            ->method('storeToken')
            ->willReturn(true);
        $this->assertFalse($this->signUpCommand->execute());
    }

    public function testExecuteFailureResponseIsEmpty()
    {
        $this->tokenProviderMock->expects($this->once())
            ->method('getToken')
            ->willReturn('IntegrationToken');
        $this->integrationManagerMock->expects($this->once())
            ->method('activateIntegration')
            ->willReturn(true);
        $this->signUpRequestMock->expects($this->once())
            ->method('call')
            ->with('IntegrationToken')
            ->willReturn(false);
        $this->analyticsTokenMock->expects($this->never())
            ->method('storeToken');
        $this->assertFalse($this->signUpCommand->execute());
    }
}
