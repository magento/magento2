<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Oauth\Oauth;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Request;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\Integration;
use Magento\Webapi\Model\Authorization\OauthUserContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\Webapi\Model\Authorization\OauthUserContext
 */
class OauthUserContextTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var OauthUserContext
     */
    protected $oauthUserContext;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \Magento\Framework\Oauth\Helper\Request
     */
    protected $oauthRequestHelper;

    /**
     * @var IntegrationServiceInterface
     */
    protected $integrationService;

    /**
     * @var Oauth
     */
    protected $oauthService;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConsumerId'])
            ->getMock();

        $this->integrationService = $this->getMockBuilder(IntegrationServiceInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'findByName',
                    'update',
                    'create',
                    'get',
                    'findByConsumerId',
                    'findActiveIntegrationByConsumerId',
                    'delete',
                    'getSelectedResources'
                ]
            )
            ->getMockForAbstractClass();

        $this->oauthRequestHelper = $this->getMockBuilder(\Magento\Framework\Oauth\Helper\Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareRequest', 'getRequestUrl'])
            ->getMock();

        $this->oauthService = $this->getMockBuilder(Oauth::class)
            ->disableOriginalConstructor()
            ->setMethods(['validateAccessTokenRequest'])
            ->getMock();

        $this->oauthUserContext = $this->objectManager->getObject(
            OauthUserContext::class,
            [
                'request' => $this->request,
                'integrationService' => $this->integrationService,
                'oauthService' => $this->oauthService,
                'oauthHelper' => $this->oauthRequestHelper
            ]
        );
    }

    public function testGetUserType()
    {
        $this->assertEquals(UserContextInterface::USER_TYPE_INTEGRATION, $this->oauthUserContext->getUserType());
    }

    public function testGetUserIdExist()
    {
        $integrationId = 12345;

        $this->setupUserId($integrationId, ['oauth_token' => 'asdcfsdvanskdcalkdsjcfljldk']);

        $this->assertEquals($integrationId, $this->oauthUserContext->getUserId());
    }

    public function testGetUserIdDoesNotExist()
    {
        $integrationId = null;

        $this->setupUserId($integrationId, ['oauth_token' => 'asdcfsdvanskdcalkdsjcfljldk']);

        $this->assertEquals($integrationId, $this->oauthUserContext->getUserId());
    }

    public function testGetUserIdNoOauthInformation()
    {
        $integrationId = 12345;

        $this->setupUserId($integrationId, []);

        $this->assertNull($this->oauthUserContext->getUserId());
    }

    /**
     * @param int|null $integrationId
     * @param array $oauthRequest
     * @return void
     */
    public function setupUserId($integrationId, $oauthRequest)
    {
        $integration = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();

        $this->integrationService->expects($this->any())
            ->method('findActiveIntegrationByConsumerId')
            ->willReturn($integration);

        $this->oauthRequestHelper->expects($this->once())
            ->method('prepareRequest')
            ->willReturn($oauthRequest);

        $this->oauthService->expects($this->any())
            ->method('validateAccessTokenRequest')
            ->willReturn(1);

        $integration->expects($this->any())
            ->method('getId')
            ->willReturn($integrationId);
    }
}
