<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Cron;

use Magento\Integration\Cron\CleanExpiredTokens;
use Magento\Integration\Model\ResourceModel\Oauth\Token as TokenResourceModel;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Test for \Magento\Integration\Cron\CleanExpiredTokens.
 */
class CleanExpiredTokensTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CleanExpiredTokens
     */
    private $cleanExpiredTokensCron;

    /**
     * @var TokenResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenResourceModelMock;

    /**
     * @var OauthHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $oauthHelperMock;

    protected function setUp()
    {
        $this->tokenResourceModelMock = $this->getMockBuilder(TokenResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->oauthHelperMock = $this->getMockBuilder(OauthHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cleanExpiredTokensCron = new CleanExpiredTokens(
            $this->tokenResourceModelMock,
            $this->oauthHelperMock
        );
    }

    public function testExecute()
    {
        $adminTokenLifeTime = 9600;
        $customerTokenLifeTime = 10000;

        $this->oauthHelperMock
            ->expects($this->once())
            ->method('getAdminTokenLifetime')
            ->willReturn($adminTokenLifeTime);
        $this->oauthHelperMock
            ->expects($this->once())
            ->method('getCustomerTokenLifetime')
            ->willReturn($customerTokenLifeTime);

        $this->tokenResourceModelMock
            ->expects($this->exactly(2))
            ->method('deleteExpiredTokens')
            ->withConsecutive(
                [$adminTokenLifeTime, [UserContextInterface::USER_TYPE_ADMIN]],
                [$customerTokenLifeTime, [UserContextInterface::USER_TYPE_CUSTOMER]]
            )->willReturnOnConsecutiveCalls(1, 2);

        $this->cleanExpiredTokensCron->execute();
    }
}
