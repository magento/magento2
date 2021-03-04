<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model\Authorization;

use Magento\Webapi\Model\Authorization\TokenUserContext;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Framework\Webapi\Request;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;
use Magento\Framework\Stdlib\DateTime\DateTime as Date;
use Magento\Framework\Stdlib\DateTime;
use Magento\Integration\Model\Integration;

/**
 * Tests for TokenUserContext.
 */
class TokenUserContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var TokenUserContext
     */
    protected $tokenUserContext;

    /**
     * @var TokenFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $tokenFactory;

    /**
     * @var IntegrationServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $integrationService;

    /**
     * @var Request|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var OauthHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $oauthHelperMock;

    /**
     * @var Date|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dateMock;

    /**
     * @var DateTime|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dateTimeMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHeader'])
            ->getMock();

        $this->tokenFactory = $this->getMockBuilder(TokenFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
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
                    'getSelectedResources',
                ]
            )
            ->getMockForAbstractClass();

        $this->oauthHelperMock = $this->getMockBuilder(OauthHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdminTokenLifetime', 'getCustomerTokenLifetime'])
            ->getMock();

        $this->dateMock = $this->getMockBuilder(Date::class)
            ->disableOriginalConstructor()
            ->setMethods(['gmtTimestamp'])
            ->getMock();

        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->setMethods(['strToTime'])
            ->getMock();

        $this->dateTimeMock->expects($this->any())
            ->method('strToTime')
            ->willReturnCallback(
                
                    function ($str) {
                        return strtotime($str);
                    }
                
            );

        $this->tokenUserContext = $this->objectManager->getObject(
            TokenUserContext::class,
            [
                'request' => $this->request,
                'tokenFactory' => $this->tokenFactory,
                'integrationService' => $this->integrationService,
                'oauthHelper' => $this->oauthHelperMock,
                'date' => $this->dateMock,
                'dateTime' => $this->dateTimeMock,
            ]
        );
    }

    public function testNoAuthorizationHeader()
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->willReturn(null);
        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testNoTokenInHeader()
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->willReturn('Bearer');
        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testNotBearerToken()
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->willReturn('Access');
        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testNoTokenInDatabase()
    {
        $bearerToken = 'bearer1234';

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->willReturn("Bearer {$bearerToken}");

        $token = $this->getMockBuilder(Token::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadByToken', 'getId', '__wakeup'])
            ->getMock();
        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->willReturn($token);
        $token->expects($this->once())
            ->method('loadByToken')
            ->with($bearerToken)
            ->willReturnSelf();
        $token->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testRevokedToken()
    {
        $bearerToken = 'bearer1234';

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->willReturn("Bearer {$bearerToken}");

        $token = $this->getMockBuilder(Token::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadByToken', 'getId', 'getRevoked', '__wakeup'])
            ->getMock();
        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->willReturn($token);
        $token->expects($this->once())
            ->method('loadByToken')
            ->with($bearerToken)
            ->willReturnSelf();
        $token->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $token->expects($this->once())
            ->method('getRevoked')
            ->willReturn(1);

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
            ->willReturn("Bearer {$bearerToken}");

        $token = $this->getMockBuilder(Token::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'loadByToken',
                    'getId',
                    'getUserType',
                    'getCustomerId',
                    'getAdminId',
                    '__wakeup',
                    'getCreatedAt',
                ]
            )->getMock();
        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->willReturn($token);
        $token->expects($this->once())
            ->method('loadByToken')
            ->with($bearerToken)
            ->willReturnSelf();
        $token->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $token->expects($this->any())
            ->method('getUserType')
            ->willReturn($userType);

        $token->expects($this->any())
            ->method('getCreatedAt')
            ->willReturn(date('Y-m-d H:i:s', time()));

        switch ($userType) {
            case UserContextInterface::USER_TYPE_INTEGRATION:
                $integration = $this->getMockBuilder(Integration::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['getId', '__wakeup'])
                    ->getMock();

                $integration->expects($this->once())
                    ->method('getId')
                    ->willReturn($userId);
                $this->integrationService->expects($this->once())
                    ->method('findByConsumerId')
                    ->willReturn($integration);
                break;
            case UserContextInterface::USER_TYPE_ADMIN:
                $token->expects($this->once())
                    ->method('getAdminId')
                    ->willReturn($userId);
                break;
            case UserContextInterface::USER_TYPE_CUSTOMER:
                $token->expects($this->once())
                    ->method('getCustomerId')
                    ->willReturn($userId);
                break;
        }

        $this->assertEquals($expectedUserType, $this->tokenUserContext->getUserType());
        $this->assertEquals($expectedUserId, $this->tokenUserContext->getUserId());

        /* check again to make sure that the above methods were only called once */
        $this->assertEquals($expectedUserType, $this->tokenUserContext->getUserType());
        $this->assertEquals($expectedUserId, $this->tokenUserContext->getUserId());
    }

    /**
     * @return array
     */
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

    /**
     * @param array $tokenData
     * @param int $tokenTtl
     * @param int $currentTime
     * @param int|null $expectedUserType
     * @param int|null $expectedUserId
     * @return void
     * @dataProvider getExpiredTestTokenData
     */
    public function testExpiredToken(
        array $tokenData,
        int $tokenTtl,
        int $currentTime,
        $expectedUserType,
        $expectedUserId
    ) {
        $bearerToken = 'bearer1234';

        $this->dateMock->expects($this->any())
            ->method('gmtTimestamp')
            ->willReturn($currentTime);

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->willReturn("Bearer {$bearerToken}");

        $token = $this->getMockBuilder(Token::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'loadByToken',
                    'getCreatedAt',
                    'getId',
                    'getUserType',
                    'getCustomerId',
                    'getAdminId',
                    '__wakeup',
                ]
            )->getMock();

        $token->expects($this->once())
            ->method('loadByToken')
            ->with($bearerToken)
            ->willReturnSelf();

        $token->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $token->expects($this->any())
            ->method('getUserType')
            ->willReturn($tokenData['user_type']);

        $token->expects($this->any())
            ->method('getCreatedAt')
            ->willReturn($tokenData['created_at']);

        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->willReturn($token);

        $this->oauthHelperMock->expects($this->any())
            ->method('getAdminTokenLifetime')
            ->willReturn($tokenTtl);

        $this->oauthHelperMock->expects($this->any())
            ->method('getCustomerTokenLifetime')
            ->willReturn($tokenTtl);

        switch ($tokenData['user_type']) {
            case UserContextInterface::USER_TYPE_INTEGRATION:
                $integration = $this->getMockBuilder(Integration::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['getId', '__wakeup'])
                    ->getMock();
                $integration->expects($this->any())
                    ->method('getId')
                    ->willReturn($tokenData['user_id']);

                $this->integrationService->expects($this->any())
                    ->method('findByConsumerId')
                    ->willReturn($integration);
                break;
            case UserContextInterface::USER_TYPE_ADMIN:
                $token->expects($this->any())
                    ->method('getAdminId')
                    ->willReturn($tokenData['user_id']);
                break;
            case UserContextInterface::USER_TYPE_CUSTOMER:
                $token->expects($this->any())
                    ->method('getCustomerId')
                    ->willReturn($tokenData['user_id']);
                break;
        }

        $this->assertEquals($expectedUserType, $this->tokenUserContext->getUserType());
        $this->assertEquals($expectedUserId, $this->tokenUserContext->getUserId());

        /* check again to make sure that the above method loadByToken in only called once */
        $this->assertEquals($expectedUserType, $this->tokenUserContext->getUserType());
        $this->assertEquals($expectedUserId, $this->tokenUserContext->getUserId());
    }

    /**
     * Data provider for expired token test.
     *
     * @return array
     */
    public function getExpiredTestTokenData()
    {
        $time = time();
        return [
            'token_expired_admin' => [
                'tokenData' => [
                    'user_type' => UserContextInterface::USER_TYPE_ADMIN,
                    'user_id' => 1234,
                    'created_at' => date('Y-m-d H:i:s', $time - 3600 - 400),
                ],
                'tokenTtl' => 1,
                'currentTime' => $time,
                'expectedUserType' => null,
                'expectedUserId' => null,
            ],
            'token_vigent_admin' => [
                'tokenData' => [
                    'user_type' => UserContextInterface::USER_TYPE_ADMIN,
                    'user_id' => 1234,
                    'created_at' => date('Y-m-d H:i:s', $time - 400),
                ],
                'tokenTtl' => 1,
                'currentTime' => $time,
                'expectedUserType' => UserContextInterface::USER_TYPE_ADMIN,
                'expectedUserId' => 1234,
            ],
            'token_expired_customer' => [
                'tokenData' => [
                    'user_type' => UserContextInterface::USER_TYPE_CUSTOMER,
                    'user_id' => 1234,
                    'created_at' => date('Y-m-d H:i:s', $time - 3600 - 400),
                ],
                'tokenTtl' => 1,
                'currentTime' => $time,
                'expectedUserType' => null,
                'expectedUserId' => null,
            ],
            'token_vigent_customer' => [
                'tokenData' => [
                    'user_type' => UserContextInterface::USER_TYPE_CUSTOMER,
                    'user_id' => 1234,
                    'created_at' => date('Y-m-d H:i:s', $time - 400),
                ],
                'tokenTtl' => 1,
                'currentTime' => $time,
                'expectedUserType' => UserContextInterface::USER_TYPE_CUSTOMER,
                'expectedUserId' => 1234,
            ],
            'token_expired_integration' => [
                'tokenData' => [
                    'user_type' => UserContextInterface::USER_TYPE_INTEGRATION,
                    'user_id' => 1234,
                    'created_at' => date('Y-m-d H:i:s', $time - 3600 - 400),
                ],
                'tokenTtl' => 1,
                'currentTime' => $time,
                'expectedUserType' => UserContextInterface::USER_TYPE_INTEGRATION,
                'expectedUserId' => 1234,
            ],
            'token_vigent_integration' => [
                'tokenData' => [
                    'user_type' => UserContextInterface::USER_TYPE_INTEGRATION,
                    'user_id' => 1234,
                    'created_at' => date('Y-m-d H:i:s', $time - 400),
                ],
                'tokenTtl' => 1,
                'currentTime' => $time,
                'expectedUserType' => UserContextInterface::USER_TYPE_INTEGRATION,
                'expectedUserId' => 1234,
            ],
            'token_expired_guest' => [
                'tokenData' => [
                    'user_type' => UserContextInterface::USER_TYPE_GUEST,
                    'user_id' => 1234,
                    'created_at' => date('Y-m-d H:i:s', $time - 3600 - 400),
                ],
                'tokenTtl' => 1,
                'currentTime' => $time,
                'expectedUserType' => null,
                'expectedUserId' => null,
            ],
            'token_vigent_guest' => [
                'tokenData' => [
                    'user_type' => UserContextInterface::USER_TYPE_GUEST,
                    'user_id' => 1234,
                    'created_at' => date('Y-m-d H:i:s', $time - 400),
                ],
                'tokenTtl' => 1,
                'currentTime' => $time,
                'expectedUserType' => null,
                'expectedUserId' => null,
            ],
        ];
    }
}
