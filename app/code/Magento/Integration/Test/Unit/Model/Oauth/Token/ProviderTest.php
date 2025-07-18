<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Oauth\Token;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Oauth\ConsumerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Integration\Model\Oauth\ConsumerFactory;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\Token\Provider;
use Magento\Integration\Model\Oauth\TokenFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for \Magento\Integration\Model\Oauth\Token\Provider
 */
class ProviderTest extends TestCase
{
    /** @var Provider */
    protected $tokenProvider;

    /** @var ConsumerFactory|MockObject */
    protected $consumerFactoryMock;

    /** @var TokenFactory|MockObject */
    protected $tokenFactoryMock;

    /** @var LoggerInterface|MockObject */
    protected $loggerMock;

    /** @var ConsumerInterface|MockObject */
    protected $consumerMock;

    /** @var Token|MockObject */
    protected $requestTokenMock;

    /** @var Token|MockObject */
    protected $accessTokenMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->consumerFactoryMock = $this->getMockBuilder(ConsumerFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenFactoryMock = $this->getMockBuilder(TokenFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->consumerMock = $this->getMockBuilder(ConsumerInterface::class)
            ->addMethods([
                'load',
                'loadByKey'
            ])
            ->onlyMethods(
                [
                    'validate',
                    'getId',
                    'getKey',
                    'getSecret',
                    'getCallbackUrl',
                    'getCreatedAt',
                    'isValidForTokenExchange'
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestTokenMock = $this->getMockBuilder(Token::class)
            ->addMethods([
                'getConsumerId',
                'getType',
                'getSecret',
                'getToken',
            ])
            ->onlyMethods(
                [
                    'loadByConsumerIdAndUserType',
                    'load',
                    'getId',
                    'getVerifier',
                    'createRequestToken',
                    'convertToAccess'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->accessTokenMock = $this->getMockBuilder(Token::class)
            ->onlyMethods([
                'load',
                'getId',
            ])
            ->addMethods(
                [
                    'getToken',
                    'getSecret',
                    'getConsumerId',
                    'getType',
                    'getRevoked'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenProvider = $objectManagerHelper->getObject(
            Provider::class,
            [
                'consumerFactory' => $this->consumerFactoryMock,
                'tokenFactory' => $this->tokenFactoryMock,
                'logger' => $this->loggerMock,
            ]
        );
    }

    public function testValidateConsumer()
    {
        $this->consumerMock->expects($this->once())->method('isValidForTokenExchange')->willReturn(true);
        $this->assertTrue($this->tokenProvider->validateConsumer($this->consumerMock));
    }

    public function testValidateConsumerException()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Consumer key has expired');
        $this->consumerMock->expects($this->once())->method('isValidForTokenExchange')->willReturn(false);
        $this->tokenProvider->validateConsumer($this->consumerMock);
    }

    public function testGetIntegrationTokenByConsumerId()
    {
        $consumerId = 1;
        $tokenId = 1;

        $this->requestTokenMock->expects($this->once())
            ->method('loadByConsumerIdAndUserType')
            ->with($consumerId, UserContextInterface::USER_TYPE_INTEGRATION);
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);

        $this->requestTokenMock->expects($this->once())->method('getId')->willReturn($tokenId);

        $actualToken = $this->tokenProvider->getIntegrationTokenByConsumerId($consumerId);
        $this->assertEquals($this->requestTokenMock, $actualToken);
    }

    public function testGetIntegrationTokenByConsumerIdException()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('A token with consumer ID 1 does not exist');
        $consumerId = 1;
        $tokenId = false;

        $this->requestTokenMock->expects($this->once())
            ->method('loadByConsumerIdAndUserType')
            ->with($consumerId, UserContextInterface::USER_TYPE_INTEGRATION);
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);

        $this->requestTokenMock->expects($this->once())->method('getId')->willReturn($tokenId);

        $this->tokenProvider->getIntegrationTokenByConsumerId($consumerId);
    }

    public function testCreateRequestToken()
    {
        $consumerId = 1;
        $tokenId = 1;
        $tokenString = '12345678901234567890123456789012';
        $secret = 'secret';

        $tokenMock = $this->getMockBuilder(Token::class)
            ->addMethods(['getType'])
            ->onlyMethods(
                [
                    'loadByConsumerIdAndUserType',
                    'getId',
                    'createRequestToken'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $tokenMock->expects($this->once())
            ->method('loadByConsumerIdAndUserType')
            ->with($consumerId, UserContextInterface::USER_TYPE_INTEGRATION);
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($tokenMock);

        $tokenMock->expects($this->any())->method('getId')->willReturn($tokenId);
        $tokenMock->expects($this->once())->method('createRequestToken')->willReturn(
            $this->requestTokenMock
        );
        $tokenMock->expects($this->any())->method('getType')->willReturn(Token::TYPE_VERIFIER);

        $this->consumerMock->expects($this->once())->method('getId')->willReturn($consumerId);
        $this->consumerMock->expects($this->once())->method('getCallbackUrl');

        $this->requestTokenMock->expects($this->any())->method('getToken')->willReturn($tokenString);
        $this->requestTokenMock->expects($this->any())->method('getSecret')->willReturn($secret);
        $response = $this->tokenProvider->createRequestToken($this->consumerMock);

        $this->assertArrayHasKey('oauth_token', $response);
        $this->assertArrayHasKey('oauth_token_secret', $response);
        $this->assertEquals($tokenString, $response['oauth_token']);
        $this->assertEquals($secret, $response['oauth_token_secret']);
    }

    public function testCreateRequestTokenIncorrectType()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Cannot create request token because consumer token is not a verifier token');
        $consumerId = 1;
        $tokenId = 1;

        $tokenMock = $this->getMockBuilder(Token::class)
            ->addMethods([
                'getType',
            ])
            ->onlyMethods(
                [
                    'loadByConsumerIdAndUserType',
                    'getId',
                    'createRequestToken'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $tokenMock->expects($this->once())
            ->method('loadByConsumerIdAndUserType')
            ->with($consumerId, UserContextInterface::USER_TYPE_INTEGRATION);
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($tokenMock);

        $tokenMock->expects($this->any())->method('getId')->willReturn($tokenId);
        $tokenMock->expects($this->any())->method('getType')->willReturn('incorrectType');

        $this->consumerMock->expects($this->once())->method('getId')->willReturn($consumerId);

        $this->tokenProvider->createRequestToken($this->consumerMock);
    }

    public function testGetAccessToken()
    {
        $consumerId = 1;
        $tokenId = 1;
        $tokenString = '12345678901234567890123456789012';
        $secret = 'secret';

        $this->consumerMock->expects($this->once())->method('getId')->willReturn($consumerId);

        $this->requestTokenMock->expects($this->once())
            ->method('loadByConsumerIdAndUserType')
            ->with($consumerId, UserContextInterface::USER_TYPE_INTEGRATION);
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);

        $this->requestTokenMock->expects($this->once())->method('getId')->willReturn($tokenId);
        $this->requestTokenMock->expects($this->once())->method('getType')->willReturn(Token::TYPE_REQUEST);
        $this->requestTokenMock->expects($this->once())->method('convertToAccess')->willReturn($this->accessTokenMock);
        $this->accessTokenMock->expects($this->once())->method('getToken')->willReturn($tokenString);
        $this->accessTokenMock->expects($this->once())->method('getSecret')->willReturn($secret);

        $response = $this->tokenProvider->getAccessToken($this->consumerMock);
        $this->assertArrayHasKey('oauth_token', $response);
        $this->assertArrayHasKey('oauth_token_secret', $response);
        $this->assertEquals($tokenString, $response['oauth_token']);
        $this->assertEquals($secret, $response['oauth_token_secret']);
    }

    public function testGetAccessTokenIsNotRequestToken()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Cannot get access token because consumer token is not a request token');
        $consumerId = 1;
        $tokenId = 1;

        $this->consumerMock->expects($this->once())->method('getId')->willReturn($consumerId);

        $this->requestTokenMock->expects($this->once())
            ->method('loadByConsumerIdAndUserType')
            ->with($consumerId, UserContextInterface::USER_TYPE_INTEGRATION);
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);

        $this->requestTokenMock->expects($this->once())->method('getId')->willReturn($tokenId);
        $this->requestTokenMock->expects($this->once())->method('getType')->willReturn('isNotRequestToken');

        $this->tokenProvider->getAccessToken($this->consumerMock);
    }

    public function testValidateRequestToken()
    {
        $requestTokenString = '12345678901234567890123456789012';
        $oauthVerifier = '12345678901234567890123456789012';
        $consumerId = 1;
        $tokenId = 1;
        $secret = 'secret';

        $this->requestTokenMock->expects($this->once())
            ->method('load')
            ->with($requestTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);
        $this->requestTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);
        $this->requestTokenMock->expects($this->once())->method('getConsumerId')->willReturn($consumerId);

        $this->requestTokenMock->expects($this->once())->method('getType')->willReturn(Token::TYPE_REQUEST);
        $this->requestTokenMock->expects($this->once())->method('getSecret')->willReturn($secret);
        $this->requestTokenMock->expects($this->once())->method('getVerifier')->willReturn($oauthVerifier);

        $this->assertEquals(
            $secret,
            $this->tokenProvider->validateRequestToken($requestTokenString, $this->consumerMock, $oauthVerifier)
        );
    }

    public function testValidateRequestTokenNotExistentToken()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Specified token does not exist');
        $requestTokenString = '12345678901234567890123456789012';
        $oauthVerifier = '12345678901234567890123456789012';

        $this->requestTokenMock->expects($this->once())
            ->method('load')
            ->with($requestTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);
        $this->requestTokenMock->expects($this->any())->method('getId')->willReturn(0);

        $this->tokenProvider->validateRequestToken($requestTokenString, $this->consumerMock, $oauthVerifier);
    }

    public function testValidateRequestTokenIncorrectLengthToken()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('The token length is invalid. Check the length and try again.');
        $requestTokenString = '123';
        $oauthVerifier = '12345678901234567890123456789012';

        $this->tokenProvider->validateRequestToken($requestTokenString, $this->consumerMock, $oauthVerifier);
    }

    public function testValidateRequestTokenInvalidVerifier()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Verifier is invalid');
        $requestTokenString = '12345678901234567890123456789012';
        $oauthVerifier = 1;
        $consumerId = 1;
        $tokenId = 1;

        $this->requestTokenMock->expects($this->once())
            ->method('load')
            ->with($requestTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);
        $this->requestTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);
        $this->requestTokenMock->expects($this->once())->method('getConsumerId')->willReturn($consumerId);

        $this->requestTokenMock->expects($this->once())->method('getType')->willReturn(Token::TYPE_REQUEST);
        $this->requestTokenMock->expects($this->once())->method('getVerifier')->willReturn($oauthVerifier);

        $this->tokenProvider->validateRequestToken($requestTokenString, $this->consumerMock, $oauthVerifier);
    }

    public function testValidateRequestTokenIncorrectLengthVerifier()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Verifier is not the correct length');
        $requestTokenString = '12345678901234567890123456789012';
        $oauthVerifier = '123';
        $consumerId = 1;
        $tokenId = 1;

        $this->requestTokenMock->expects($this->once())
            ->method('load')
            ->with($requestTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);
        $this->requestTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);
        $this->requestTokenMock->expects($this->once())->method('getConsumerId')->willReturn($consumerId);

        $this->requestTokenMock->expects($this->once())->method('getType')->willReturn(Token::TYPE_REQUEST);
        $this->requestTokenMock->expects($this->once())->method('getVerifier')->willReturn($oauthVerifier);

        $this->tokenProvider->validateRequestToken($requestTokenString, $this->consumerMock, $oauthVerifier);
    }

    public function testValidateRequestTokenNotMatchedVerifier()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Token verifier and verifier token do not match');
        $requestTokenString = '12345678901234567890123456789012';
        $oauthVerifier = '12345678901234567890123456789012';
        $notMatchedVerifier = '123';
        $consumerId = 1;
        $tokenId = 1;

        $this->requestTokenMock->expects($this->once())
            ->method('load')
            ->with($requestTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);
        $this->requestTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);
        $this->requestTokenMock->expects($this->once())->method('getConsumerId')->willReturn($consumerId);

        $this->requestTokenMock->expects($this->once())->method('getType')->willReturn(Token::TYPE_REQUEST);
        $this->requestTokenMock->expects($this->once())->method('getVerifier')->willReturn($notMatchedVerifier);

        $this->tokenProvider->validateRequestToken($requestTokenString, $this->consumerMock, $oauthVerifier);
    }

    public function testValidateRequestTokenNotAssociatedToken()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Request token is not associated with the specified consumer');
        $requestTokenString = '12345678901234567890123456789012';
        $oauthVerifier = '12345678901234567890123456789012';
        $consumerId = 1;
        $notCustomerId = 2;
        $tokenId = 1;

        $this->requestTokenMock->expects($this->once())
            ->method('load')
            ->with($requestTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);
        $this->requestTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);
        $this->requestTokenMock->expects($this->once())->method('getConsumerId')->willReturn($notCustomerId);

        $this->tokenProvider->validateRequestToken($requestTokenString, $this->consumerMock, $oauthVerifier);
    }

    public function testValidateRequestTokenAlreadyUsedToken()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Token is already being used');
        $requestTokenString = '12345678901234567890123456789012';
        $oauthVerifier = '12345678901234567890123456789012';
        $consumerId = 1;
        $tokenId = 1;

        $this->requestTokenMock->expects($this->once())
            ->method('load')
            ->with($requestTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->requestTokenMock);
        $this->requestTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);
        $this->requestTokenMock->expects($this->once())->method('getConsumerId')->willReturn($consumerId);

        $this->requestTokenMock->expects($this->once())->method('getType')->willReturn('alreadyUsedToken');

        $this->tokenProvider->validateRequestToken($requestTokenString, $this->consumerMock, $oauthVerifier);
    }

    public function testValidateAccessTokenRequest()
    {
        $accessTokenString = '12345678901234567890123456789012';
        $tokenId = 1;
        $consumerId = 1;
        $secret = 'secret';

        $this->accessTokenMock->expects($this->once())
            ->method('load')
            ->with($accessTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->accessTokenMock);
        $this->accessTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);
        $this->accessTokenMock->expects($this->once())->method('getConsumerId')->willReturn($consumerId);

        $this->accessTokenMock->expects($this->once())->method('getType')->willReturn(Token::TYPE_ACCESS);
        $this->accessTokenMock->expects($this->once())->method('getRevoked')->willReturn(0);

        $this->accessTokenMock->expects($this->once())->method('getSecret')->willReturn($secret);

        $this->assertEquals(
            $secret,
            $this->tokenProvider->validateAccessTokenRequest($accessTokenString, $this->consumerMock)
        );
    }

    public function testValidateAccessTokenRequestNotAssociatedToken()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Token is not associated with the specified consumer');
        $accessTokenString = '12345678901234567890123456789012';
        $tokenId = 1;
        $consumerId = 1;
        $notCustomerId = 2;

        $this->accessTokenMock->expects($this->once())
            ->method('load')
            ->with($accessTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->accessTokenMock);
        $this->accessTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);
        $this->accessTokenMock->expects($this->once())->method('getConsumerId')->willReturn($notCustomerId);

        $this->tokenProvider->validateAccessTokenRequest($accessTokenString, $this->consumerMock);
    }

    public function testValidateAccessTokenRequestNotAccessToken()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Token is not an access token');
        $accessTokenString = '12345678901234567890123456789012';
        $tokenId = 1;
        $consumerId = 1;

        $this->accessTokenMock->expects($this->once())
            ->method('load')
            ->with($accessTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->accessTokenMock);
        $this->accessTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);
        $this->accessTokenMock->expects($this->once())->method('getConsumerId')->willReturn($consumerId);

        $this->accessTokenMock->expects($this->once())->method('getType')->willReturn('notAccessToken');

        $this->tokenProvider->validateAccessTokenRequest($accessTokenString, $this->consumerMock);
    }

    public function testValidateAccessTokenRequestRevokedToken()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Access token has been revoked');
        $accessTokenString = '12345678901234567890123456789012';
        $tokenId = 1;
        $consumerId = 1;

        $this->accessTokenMock->expects($this->once())
            ->method('load')
            ->with($accessTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->accessTokenMock);
        $this->accessTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);
        $this->accessTokenMock->expects($this->once())->method('getConsumerId')->willReturn($consumerId);

        $this->accessTokenMock->expects($this->once())->method('getType')->willReturn(Token::TYPE_ACCESS);
        $this->accessTokenMock->expects($this->once())->method('getRevoked')->willReturn(1);

        $this->tokenProvider->validateAccessTokenRequest($accessTokenString, $this->consumerMock);
    }

    public function testValidateAccessToken()
    {
        $accessTokenString = '12345678901234567890123456789012';
        $tokenId = 1;
        $consumerId = 1;

        $this->accessTokenMock->expects($this->once())
            ->method('load')
            ->with($accessTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->accessTokenMock);
        $this->accessTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->accessTokenMock->expects($this->any())->method('getConsumerId')->willReturn($consumerId);

        $this->consumerFactoryMock->expects($this->any())->method('create')->willReturn($this->consumerMock);
        $this->consumerMock->expects($this->any())->method('load')->willReturnSelf();
        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);

        $this->accessTokenMock->expects($this->once())->method('getType')->willReturn(Token::TYPE_ACCESS);
        $this->accessTokenMock->expects($this->once())->method('getRevoked')->willReturn(0);
        $this->assertEquals(
            $consumerId,
            $this->tokenProvider->validateAccessToken($accessTokenString)
        );
    }

    public function testValidateAccessTokenNotExistentConsumer()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('A consumer with the ID 1 does not exist');
        $accessTokenString = '12345678901234567890123456789012';
        $tokenId = 1;
        $consumerId = 1;

        $this->accessTokenMock->expects($this->once())
            ->method('load')
            ->with($accessTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->accessTokenMock);
        $this->accessTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->accessTokenMock->expects($this->any())->method('getConsumerId')->willReturn($consumerId);

        $this->consumerFactoryMock->expects($this->any())->method('create')->willReturn($this->consumerMock);
        $this->consumerMock->expects($this->any())->method('load')->willReturnSelf();
        $this->consumerMock->expects($this->any())->method('getId')->willReturn(0);

        $this->tokenProvider->validateAccessToken($accessTokenString);
    }

    public function testValidateAccessTokenNotAccessToken()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Token is not an access token');
        $accessTokenString = '12345678901234567890123456789012';
        $tokenId = 1;
        $consumerId = 1;

        $this->accessTokenMock->expects($this->once())
            ->method('load')
            ->with($accessTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->accessTokenMock);
        $this->accessTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->accessTokenMock->expects($this->any())->method('getConsumerId')->willReturn($consumerId);

        $this->consumerFactoryMock->expects($this->any())->method('create')->willReturn($this->consumerMock);
        $this->consumerMock->expects($this->any())->method('load')->willReturnSelf();
        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);

        $this->accessTokenMock->expects($this->once())->method('getType')->willReturn('notAccessToken');
        $this->tokenProvider->validateAccessToken($accessTokenString);
    }

    public function testValidateAccessTokenRevoked()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Access token has been revoked');
        $accessTokenString = '12345678901234567890123456789012';
        $tokenId = 1;
        $consumerId = 1;

        $this->accessTokenMock->expects($this->once())
            ->method('load')
            ->with($accessTokenString, 'token')
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())->method('create')->willReturn($this->accessTokenMock);
        $this->accessTokenMock->expects($this->any())->method('getId')->willReturn($tokenId);

        $this->accessTokenMock->expects($this->any())->method('getConsumerId')->willReturn($consumerId);

        $this->consumerFactoryMock->expects($this->any())->method('create')->willReturn($this->consumerMock);
        $this->consumerMock->expects($this->any())->method('load')->willReturnSelf();
        $this->consumerMock->expects($this->any())->method('getId')->willReturn($consumerId);

        $this->accessTokenMock->expects($this->once())->method('getType')->willReturn(Token::TYPE_ACCESS);
        $this->accessTokenMock->expects($this->once())->method('getRevoked')->willReturn(1);

        $this->tokenProvider->validateAccessToken($accessTokenString);
    }

    public function testValidateOauthToken()
    {
        $tokenString = '12345678901234567890123456789012';
        $this->assertTrue($this->tokenProvider->validateOauthToken($tokenString));
    }

    public function testGetConsumerByKey()
    {
        $consumerKeyString = '12345678901234567890123456789012';
        $consumerId = 1;

        $this->consumerFactoryMock->expects($this->once())->method('create')->willReturn($this->consumerMock);
        $this->consumerMock->expects($this->once())->method('loadByKey')->with($consumerKeyString)->willReturnSelf();
        $this->consumerMock->expects($this->once())->method('getId')->willReturn($consumerId);

        $this->assertEquals($this->consumerMock, $this->tokenProvider->getConsumerByKey($consumerKeyString));
    }

    public function testGetConsumerByKeyWrongConsumerKey()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('Consumer key is not the correct length');
        $consumerKeyString = '123';
        $this->tokenProvider->getConsumerByKey($consumerKeyString);
    }

    public function testGetConsumerByKeyNonExistentConsumer()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->expectExceptionMessage('A consumer having the specified key does not exist');
        $consumerKeyString = '12345678901234567890123456789012';
        $consumerId = null;

        $this->consumerFactoryMock->expects($this->once())->method('create')->willReturn($this->consumerMock);
        $this->consumerMock->expects($this->once())->method('loadByKey')->with($consumerKeyString)->willReturnSelf();
        $this->consumerMock->expects($this->once())->method('getId')->willReturn($consumerId);

        $this->tokenProvider->getConsumerByKey($consumerKeyString);
    }
}
