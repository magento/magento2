<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Tests \Magento\Webapi\Model\Authorization\TokenUserContext
 */
class TokenUserContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Webapi\Model\Authorization\TokenUserContext
     */
    protected $tokenUserContext;

    /**
     * @var \Magento\Integration\Model\Oauth\TokenFactory
     */
    protected $tokenFactory;

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    protected $integrationService;

    /**
     * @var \Magento\Framework\Webapi\Request
     */
    protected $request;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->request = $this->getMockBuilder(\Magento\Framework\Webapi\Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHeader'])
            ->getMock();

        $this->tokenFactory = $this->getMockBuilder(\Magento\Integration\Model\Oauth\TokenFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->integrationService = $this->getMockBuilder(\Magento\Integration\Api\IntegrationServiceInterface::class)
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
            ->getMock();

        $this->tokenUserContext = $this->objectManager->getObject(
            \Magento\Webapi\Model\Authorization\TokenUserContext::class,
            [
                'request' => $this->request,
                'tokenFactory' => $this->tokenFactory,
                'integrationService' => $this->integrationService
            ]
        );
    }

    public function testNoAuthorizationHeader()
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue(null));
        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testNoTokenInHeader()
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue('Bearer'));
        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testNotBearerToken()
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue('Access'));
        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testNoTokenInDatabase()
    {
        $bearerToken = 'bearer1234';

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue("Bearer {$bearerToken}"));

        $token = $this->getMockBuilder(\Magento\Integration\Model\Oauth\Token::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadByToken', 'getId', '__wakeup'])
            ->getMock();
        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token));
        $token->expects($this->once())
            ->method('loadByToken')
            ->with($bearerToken)
            ->will($this->returnSelf());
        $token->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));

        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testRevokedToken()
    {
        $bearerToken = 'bearer1234';

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue("Bearer {$bearerToken}"));

        $token = $this->getMockBuilder(\Magento\Integration\Model\Oauth\Token::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadByToken', 'getId', 'getRevoked', '__wakeup'])
            ->getMock();
        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token));
        $token->expects($this->once())
            ->method('loadByToken')
            ->with($bearerToken)
            ->will($this->returnSelf());
        $token->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $token->expects($this->once())
            ->method('getRevoked')
            ->will($this->returnValue(1));

        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    /**
     * @dataProvider getValidTokenData
     */
    public function testValidToken($userType, $userId, $expectedUserType, $expectedUserId)
    {
        $bearerToken = 'bearer1234';

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue("Bearer {$bearerToken}"));

        $token = $this->getMockBuilder(\Magento\Integration\Model\Oauth\Token::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadByToken', 'getId', 'getUserType', 'getCustomerId', 'getAdminId', '__wakeup'])
            ->getMock();
        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token));
        $token->expects($this->once())
            ->method('loadByToken')
            ->with($bearerToken)
            ->will($this->returnSelf());
        $token->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $token->expects($this->once())
            ->method('getUserType')
            ->will($this->returnValue($userType));

        $integration = $this->getMockBuilder(\Magento\Integration\Model\Integration::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();

        switch ($userType) {
            case UserContextInterface::USER_TYPE_INTEGRATION:
                $integration->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue($userId));
                $this->integrationService->expects($this->once())
                    ->method('findByConsumerId')
                    ->will($this->returnValue($integration));
                break;
            case UserContextInterface::USER_TYPE_ADMIN:
                $token->expects($this->once())
                    ->method('getAdminId')
                    ->will($this->returnValue($userId));
                break;
            case UserContextInterface::USER_TYPE_CUSTOMER:
                $token->expects($this->once())
                    ->method('getCustomerId')
                    ->will($this->returnValue($userId));
                break;
        }

        $this->assertEquals($expectedUserType, $this->tokenUserContext->getUserType());
        $this->assertEquals($expectedUserId, $this->tokenUserContext->getUserId());

        /* check again to make sure that the above methods were only called once */
        $this->assertEquals($expectedUserType, $this->tokenUserContext->getUserType());
        $this->assertEquals($expectedUserId, $this->tokenUserContext->getUserId());
    }

    public function getValidTokenData()
    {
        return [
            'admin token' => [
                UserContextInterface::USER_TYPE_ADMIN,
                1234,
                UserContextInterface::USER_TYPE_ADMIN,
                1234,
            ],
            'customer token' => [
                UserContextInterface::USER_TYPE_CUSTOMER,
                1234,
                UserContextInterface::USER_TYPE_CUSTOMER,
                1234,
            ],
            'integration token' => [
                UserContextInterface::USER_TYPE_INTEGRATION,
                1234,
                UserContextInterface::USER_TYPE_INTEGRATION,
                1234,
            ],
            'guest user type' => [
                UserContextInterface::USER_TYPE_GUEST,
                1234,
                null,
                null,
            ]
        ];
    }
}
