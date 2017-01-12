<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\TokenGenerator;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Analytics\Model\AnalyticsApiUserProvider;
use Magento\Integration\Model\Integration;

/**
 * Class TokenGeneratorTest
 */
class TokenGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $integrationServiceMock;

    /**
     * @var OauthServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $oauthServiceMock;

    /**
     * @var AnalyticsApiUserProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsApiUserProviderMock;
    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    protected function setUp()
    {
        $this->integrationServiceMock =  $this->getMockBuilder(IntegrationServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->oauthServiceMock =  $this->getMockBuilder(OauthServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->analyticsApiUserProviderMock =  $this->getMockBuilder(AnalyticsApiUserProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenGenerator = new TokenGenerator(
            $this->integrationServiceMock,
            $this->oauthServiceMock,
            $this->analyticsApiUserProviderMock
        );
    }

    public function testExecute()
    {
        $customerId = 1;
        $integrationData = ['id' => 1, 'name' => 'Ma Api Name'];
        $this->analyticsApiUserProviderMock->expects($this->once())
            ->method('getConsumerId')
            ->willReturn($customerId);
        $this->oauthServiceMock->expects($this->once())
            ->method('createAccessToken')
            ->with($customerId, true)
            ->willReturn(true);
        $this->analyticsApiUserProviderMock->expects($this->once())
            ->method('getData')
            ->willReturn($integrationData);
        $integrationData['status'] = Integration::STATUS_ACTIVE;
        $this->integrationServiceMock->expects($this->once())
            ->method('update')
            ->with($integrationData);
        $this->assertTrue($this->tokenGenerator->execute());
    }

    public function testExecuteFalse()
    {
        $customerId = 1;
        $integrationData = ['id' => 1, 'name' => 'Ma Api Name'];
        $this->analyticsApiUserProviderMock->expects($this->once())
            ->method('getConsumerId')
            ->willReturn($customerId);
        $this->oauthServiceMock->expects($this->once())
            ->method('createAccessToken')
            ->with($customerId, true)
            ->willReturn(false);
        $this->assertFalse($this->tokenGenerator->execute());
    }
}
