<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsApiUserProvider;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Config\Model\Config;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Oauth\Token;

/**
 * Class SignUpCommandTest
 */
class AnalyticsApiUserProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $integrationServiceMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var OauthServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $oauthServiceMock;

    /**
     * @var Integration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $integrationMock;

    /**
     * @var Token|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenMock;

    /**
     * @var AnalyticsApiUserProvider
     */
    private $analyticsApiUserProvider;

    protected function setUp()
    {
        $this->integrationServiceMock =  $this->getMockBuilder(IntegrationServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock =  $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->oauthServiceMock =  $this->getMockBuilder(OauthServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->integrationMock =  $this->getMockBuilder(Integration::class)
            ->setMethods(['getConsumerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenMock =  $this->getMockBuilder(Token::class)
            ->setMethods(['getToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->analyticsApiUserProvider = new AnalyticsApiUserProvider(
            $this->integrationServiceMock,
            $this->configMock,
            $this->oauthServiceMock
        );
    }

    public function testGetConsumerId()
    {
        $this->setupIntegration(1);
        $this->assertEquals($this->analyticsApiUserProvider->getConsumerId(), 1);
    }

    public function testGetData()
    {
        $data = ["id" => 1, "name" => "Name"];
        $this->integrationMock->setData($data);
        $this->setupIntegration(1);
        $this->assertEquals($this->analyticsApiUserProvider->getData(), $data);
    }

    public function testGetToken()
    {
        $this->setupIntegration(1);
        $token = "Secret token";
        $this->tokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
        $this->oauthServiceMock->expects($this->once())
            ->method('getAccessToken')
            ->willReturn($this->tokenMock);
        $this->assertEquals($this->analyticsApiUserProvider->getToken(), $token);
    }

    public function testGetTokenError()
    {
        $this->setupIntegration(1);
        $this->oauthServiceMock->expects($this->once())
            ->method('getAccessToken')
            ->willReturn(null);
        $this->assertFalse($this->analyticsApiUserProvider->getToken());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testGetConsumerIdWithNoExsistUser()
    {
        $this->setupIntegration(null);
        $this->analyticsApiUserProvider->getConsumerId();
    }

    private function setupIntegration($customerId)
    {
        $maApiUserName = 'Magento Analytics user';
        $this->integrationMock->expects($this->any())
            ->method('getConsumerId')
            ->willReturn($customerId);
        $this->integrationServiceMock->expects($this->any())
            ->method('findByName')
            ->willReturn($this->integrationMock);
        $this->configMock->expects($this->once())
            ->method('getConfigDataValue')
            ->with(AnalyticsApiUserProvider::MAGENTO_API_USER_NAME_PATH)
            ->willReturn($maApiUserName);
    }
}
