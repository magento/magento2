<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Tests \Magento\Webapi\Model\Authorization\OauthUserContext
 */
class OauthUserContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Webapi\Model\Authorization\OauthUserContext
     */
    protected $oauthUserContext;

    /**
     * @var \Magento\Webapi\Controller\Request
     */
    protected $request;

    /**
     * @var \Magento\Framework\Oauth\Helper\Request
     */
    protected $oauthRequestHelper;

    /**
     * @var \Magento\Integration\Service\V1\Integration
     */
    protected $integrationService;

    /**
     * @var \Magento\Framework\Oauth\Oauth
     */
    protected $oauthService;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->request = $this->getMockBuilder('Magento\Webapi\Controller\Request')
            ->disableOriginalConstructor()
            ->setMethods(['getConsumerId'])
            ->getMock();

        $this->integrationService = $this->getMockBuilder('Magento\Integration\Service\V1\Integration')
            ->disableOriginalConstructor()
            ->setMethods(['findActiveIntegrationByConsumerId'])
            ->getMock();

        $this->oauthRequestHelper = $this->getMockBuilder('Magento\Framework\Oauth\Helper\Request')
            ->disableOriginalConstructor()
            ->setMethods(['prepareRequest'])
            ->getMock();

        $this->oauthService = $this->getMockBuilder('Magento\Framework\Oauth\Oauth')
            ->disableOriginalConstructor()
            ->setMethods(['validateAccessTokenRequest'])
            ->getMock();

        $this->oauthUserContext = $this->objectManager->getObject(
            'Magento\Webapi\Model\Authorization\OauthUserContext',
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

        $this->assertEquals(null, $this->oauthUserContext->getUserId());
    }

    /**
     * @param int|null $integrationId
     * @param array $oauthRequest
     * @return void
     */
    public function setupUserId($integrationId, $oauthRequest)
    {
        $integration = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();

        $this->integrationService->expects($this->any())
            ->method('findActiveIntegrationByConsumerId')
            ->will($this->returnValue($integration));

        $this->oauthRequestHelper->expects($this->once())
            ->method('prepareRequest')
            ->will($this->returnValue($oauthRequest));

        $this->oauthService->expects($this->any())
            ->method('validateAccessTokenRequest')
            ->will($this->returnValue(1));

        $integration->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($integrationId));
    }
}
