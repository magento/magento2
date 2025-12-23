<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Oauth\Oauth;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
    use MockCreationTrait;

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

        $this->request = $this->createPartialMockWithReflection(
            Request::class,
            ['getConsumerId']
        );

        $this->integrationService = $this->createPartialMock(
            IntegrationServiceInterface::class,
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
        );

        $this->oauthRequestHelper = $this->createPartialMock(
            \Magento\Framework\Oauth\Helper\Request::class,
            ['prepareRequest', 'getRequestUrl']
        );

        $this->oauthService = $this->createPartialMock(
            Oauth::class,
            ['validateAccessTokenRequest']
        );

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
        $integration = $this->createPartialMock(
            Integration::class,
            ['getId', '__wakeup']
        );

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
