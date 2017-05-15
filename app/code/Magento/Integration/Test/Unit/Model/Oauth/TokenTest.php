<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model\Oauth;

use Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory;
use Magento\Integration\Model\Oauth\Token;
use Magento\Framework\Oauth\Helper\Oauth as OauthHelper;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;

/**
 * Unit test for \Magento\Integration\Model\Oauth\Nonce
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Model\Oauth\Token
     */
    protected $tokenModel;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var KeyLengthFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $keyLengthFactoryMock;

    /**
     * @var \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorKeyLengthMock;

    /**
     * @var \Magento\Framework\Url\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Integration\Model\Oauth\ConsumerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $consumerFactoryMock;

    /**
     * @var \Magento\Integration\Helper\Oauth\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $oauthDataMock;

    /**
     * @var \Magento\Framework\Oauth\Helper\Oauth|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $oauthHelperMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->setMethods(['getEventDispatcher'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validatorKeyLengthMock = $this->getMockBuilder(
            \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength::class
        )
            ->setMethods(['isValid', 'setLength', 'setName', 'getMessages'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->keyLengthFactoryMock = $this->getMockBuilder(
            \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->validatorMock = $this->getMockBuilder(\Magento\Framework\Url\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->consumerFactoryMock = $this->getMockBuilder(\Magento\Integration\Model\Oauth\ConsumerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->oauthDataMock = $this->getMockBuilder(\Magento\Integration\Helper\Oauth\Data::class)
            ->setMethods(['isCleanupProbability', 'getCleanupExpirationPeriod'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->oauthHelperMock = $this->getMockBuilder(\Magento\Framework\Oauth\Helper\Oauth::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->setMethods(
                [
                    'getIdFieldName',
                    'deleteOldEntries',
                    '_construct',
                    'getConnection',
                    'selectTokenByType',
                    'save',
                    'selectTokenByConsumerIdAndUserType',
                    'selectTokenByAdminId',
                    'selectTokenByCustomerId',
                    'load'
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resourceMock->expects($this->any())
            ->method('getIdFieldName')
            ->willReturn('id');

        $eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->setMethods(['dispatch'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($eventManagerMock);

        $this->tokenModel = new \Magento\Integration\Model\Oauth\Token(
            $this->contextMock,
            $this->registryMock,
            $this->keyLengthFactoryMock,
            $this->validatorMock,
            $this->consumerFactoryMock,
            $this->oauthDataMock,
            $this->oauthHelperMock,
            $this->resourceMock
        );
    }

    public function testAfterSave()
    {
        $this->oauthDataMock->expects($this->once())->method('isCleanupProbability')->willReturn(true);
        $this->oauthDataMock->expects($this->once())->method('getCleanupExpirationPeriod')->willReturn(30);
        $this->resourceMock->expects($this->once())->method('deleteOldEntries')->with(30);

        $this->assertEquals($this->tokenModel, $this->tokenModel->afterSave());
    }

    public function testAfterSaveNoCleanupProbability()
    {
        $this->oauthDataMock->expects($this->once())->method('isCleanupProbability')->willReturn(false);
        $this->oauthDataMock->expects($this->never())->method('getCleanupExpirationPeriod');
        $this->resourceMock->expects($this->never())->method('deleteOldEntries');

        $this->assertEquals($this->tokenModel, $this->tokenModel->afterSave());
    }

    public function testCreateVerifierToken()
    {
        $consumerId = 1;

        $this->resourceMock->expects($this->once())
            ->method('selectTokenByType')
            ->with($consumerId, Token::TYPE_VERIFIER)
            ->willReturn(['id' => 123]);

        $this->oauthHelperMock->expects($this->never())->method('generateToken');
        $this->oauthHelperMock->expects($this->never())->method('generateTokenSecret');
        $this->oauthHelperMock->expects($this->never())->method('generateVerifier');
        $this->validatorMock->expects($this->never())->method('isValid');
        $this->keyLengthFactoryMock->expects($this->never())->method('create');
        $this->resourceMock->expects($this->never())->method('save');
        $this->assertEquals($this->tokenModel, $this->tokenModel->createVerifierToken($consumerId));
    }

    public function testCreateVerifierTokenIfNoTokenId()
    {
        $consumerId = 1;
        $secret = 'secret';
        $token = 'token';
        $verifier = 'verifier';

        $this->oauthHelperMock->expects($this->once())->method('generateTokenSecret')->willReturn($secret);
        $this->oauthHelperMock->expects($this->once())->method('generateToken')->willReturn($token);
        $this->oauthHelperMock->expects($this->once())->method('generateVerifier')->willReturn($verifier);

        $this->resourceMock->expects($this->once())
            ->method('selectTokenByType')
            ->with($consumerId, Token::TYPE_VERIFIER)
            ->willReturn([]);

        $this->tokenModel->setCallbackUrl(OauthHelper::CALLBACK_ESTABLISHED);

        $this->keyLengthFactoryMock->expects($this->once())->method('create')->willReturn(
            $this->validatorKeyLengthMock
        );
        $this->validatorKeyLengthMock->expects($this->exactly(3))->method('setLength');
        $this->validatorKeyLengthMock->expects($this->exactly(3))->method('setName');
        $this->validatorKeyLengthMock->expects($this->exactly(3))->method('isValid')->willReturn(true);
        $this->resourceMock->expects($this->once())->method('save');
        $this->assertEquals($this->tokenModel, $this->tokenModel->createVerifierToken($consumerId));
    }

    /**
     * @expectedException \Magento\Framework\Oauth\Exception
     * @expectedExceptionMessage Cannot convert to access token due to token is not request type
     */
    public function testConvertToAccessIfIsNotRequestType()
    {
        $this->tokenModel->setType('isNotRequestType');
        $this->tokenModel->convertToAccess();
    }

    public function testConvertToAccess()
    {
        $token = 'token';
        $secret = 'secret';

        $this->tokenModel->setType(Token::TYPE_REQUEST);
        $this->oauthHelperMock->expects($this->once())->method('generateToken')->willReturn($token);
        $this->oauthHelperMock->expects($this->once())->method('generateTokenSecret')->willReturn($secret);
        $this->resourceMock->expects($this->once())->method('save');

        $result = $this->tokenModel->convertToAccess();
        $this->assertEquals($this->tokenModel, $result);
        $this->assertEquals($token, $result->getToken());
        $this->assertEquals($secret, $result->getSecret());
        $this->assertEquals(UserContextInterface::USER_TYPE_INTEGRATION, $result->getUserType());
    }

    public function testCreateAdminToken()
    {
        $userId = 1;
        $token = 'token';
        $secret = 'secret';

        $this->oauthHelperMock->expects($this->once())->method('generateToken')->willReturn($token);
        $this->oauthHelperMock->expects($this->once())->method('generateTokenSecret')->willReturn($secret);
        $this->resourceMock->expects($this->once())->method('save');

        $result = $this->tokenModel->createAdminToken($userId);
        $this->assertEquals($this->tokenModel, $result);
        $this->assertEquals($token, $result->getToken());
        $this->assertEquals($secret, $result->getSecret());
        $this->assertEquals($userId, $result->getAdminId());
        $this->assertEquals(UserContextInterface::USER_TYPE_ADMIN, $result->getUserType());
    }

    public function testCreateCustomerToken()
    {
        $userId = 1;
        $token = 'token';
        $secret = 'secret';

        $this->oauthHelperMock->expects($this->once())->method('generateToken')->willReturn($token);
        $this->oauthHelperMock->expects($this->once())->method('generateTokenSecret')->willReturn($secret);
        $this->resourceMock->expects($this->once())->method('save');

        $result = $this->tokenModel->createCustomerToken($userId);
        $this->assertEquals($this->tokenModel, $result);
        $this->assertEquals($token, $result->getToken());
        $this->assertEquals($secret, $result->getSecret());
        $this->assertEquals($userId, $result->getCustomerId());
        $this->assertNotEquals($userId, $result->getAdminId());
        $this->assertEquals(UserContextInterface::USER_TYPE_CUSTOMER, $result->getUserType());
    }

    public function testCreateRequestToken()
    {
        $entityId = 1;
        $callbackUrl = OauthHelper::CALLBACK_ESTABLISHED;
        $token = 'token';
        $secret = 'secret';

        $this->oauthHelperMock->expects($this->once())->method('generateTokenSecret')->willReturn($secret);
        $this->oauthHelperMock->expects($this->once())->method('generateToken')->willReturn($token);

        $this->tokenModel->setCallbackUrl($callbackUrl);
        $this->keyLengthFactoryMock->expects($this->once())->method('create')->willReturn(
            $this->validatorKeyLengthMock
        );
        $this->validatorKeyLengthMock->expects($this->exactly(2))->method('setLength');
        $this->validatorKeyLengthMock->expects($this->exactly(2))->method('setName');
        $this->validatorKeyLengthMock->expects($this->exactly(2))->method('isValid')->willReturn(true);
        $this->resourceMock->expects($this->once())->method('save');

        $actualToken = $this->tokenModel->createRequestToken($entityId, $callbackUrl);
        $this->assertEquals($this->tokenModel, $actualToken);
        $this->assertEquals($this->tokenModel->getSecret(), $actualToken->getSecret());
        $this->assertEquals($this->tokenModel->getToken(), $actualToken->getToken());
    }

    public function testToString()
    {
        $token = 'token';
        $secret = 'secret';
        $expectedResponse = "oauth_token={$token}&oauth_token_secret={$secret}";

        $this->tokenModel->setToken($token)->setSecret($secret);

        $this->assertEquals($expectedResponse, sprintf($this->tokenModel));
    }

    public function testBeforeSave()
    {
        $this->assertEquals($this->tokenModel, $this->tokenModel->beforeSave());
    }

    public function testGetVerifier()
    {
        $verifier = 'testVerifier';
        $this->tokenModel->setData('verifier', $verifier);
        $this->assertEquals($verifier, $this->tokenModel->getVerifier());
    }

    public function testLoadByConsumerIdAndUserType()
    {
        $consumerId = 1;
        $userType = 1;
        $tokenData = 'testToken';
        $data = ['token' => $tokenData];

        $this->resourceMock->expects($this->once())->method('selectTokenByConsumerIdAndUserType')->willReturn($data);
        $actualToken = $this->tokenModel->loadByConsumerIdAndUserType($consumerId, $userType);
        $this->assertEquals($this->tokenModel, $actualToken);
        $this->assertEquals($tokenData, $actualToken->getToken());
    }

    public function testLoadByAdminId()
    {
        $adminId = 1;
        $tokenData = 'testToken';
        $data = ['token' => $tokenData];

        $this->resourceMock->expects($this->once())->method('selectTokenByAdminId')->willReturn($data);
        $actualToken = $this->tokenModel->loadByAdminId($adminId);
        $this->assertEquals($this->tokenModel, $actualToken);
        $this->assertEquals($tokenData, $actualToken->getToken());
    }

    public function testLoadByCustomerId()
    {
        $customerId = 1;
        $tokenData = 'testToken';
        $data = ['token' => $tokenData];

        $this->resourceMock->expects($this->once())->method('selectTokenByCustomerId')->willReturn($data);
        $actualToken = $this->tokenModel->loadByCustomerId($customerId);
        $this->assertEquals($this->tokenModel, $actualToken);
        $this->assertEquals($tokenData, $actualToken->getToken());
    }

    public function testLoad()
    {
        $token = 'testToken';

        $this->resourceMock->expects($this->once())->method('load');
        $actualToken = $this->tokenModel->loadByToken($token);
        $this->assertEquals($this->tokenModel, $actualToken);
    }

    public function testValidateIfNotCallbackEstablishedAndNotValid()
    {
        $exceptionMessage = 'exceptionMessage';

        $this->tokenModel->setCallbackUrl('notCallbackEstablished');
        $this->validatorMock->expects($this->once())->method('isValid')->willReturn(false);
        $this->validatorMock->expects($this->once())->method('getMessages')->willReturn([$exceptionMessage]);

        $this->setExpectedException(\Magento\Framework\Oauth\Exception::class, $exceptionMessage);

        $this->tokenModel->validate();
    }

    public function testValidateIfSecretNotValid()
    {
        $exceptionMessage = 'exceptionMessage';

        $this->tokenModel->setCallbackUrl('notCallbackEstablished');
        $this->validatorMock->expects($this->once())->method('isValid')->willReturn(true);

        $this->keyLengthFactoryMock->expects($this->once())->method('create')->willReturn(
            $this->validatorKeyLengthMock
        );
        $this->validatorKeyLengthMock->expects($this->once())->method('isValid')->willReturn(false);
        $this->validatorKeyLengthMock->expects($this->once())->method('getMessages')->willReturn([$exceptionMessage]);

        $this->setExpectedException(\Magento\Framework\Oauth\Exception::class, $exceptionMessage);

        $this->tokenModel->validate();
    }

    public function testValidateIfTokenNotValid()
    {
        $exceptionMessage = 'exceptionMessage';
        $token = 'token';
        $secret = 'secret';

        $this->tokenModel->setCallbackUrl('notCallbackEstablished');
        $this->validatorMock->expects($this->once())->method('isValid')->willReturn(true);

        $this->keyLengthFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->validatorKeyLengthMock);

        $this->tokenModel->setSecret($secret);
        $this->tokenModel->setToken($token);
        $this->validatorKeyLengthMock->expects($this->exactly(2))->method('isValid')->willReturnMap(
            [
                [$secret, true],
                [$token, false]
            ]
        );
        $this->validatorKeyLengthMock->expects($this->once())->method('getMessages')->willReturn([$exceptionMessage]);
        $this->setExpectedException(\Magento\Framework\Oauth\Exception::class, $exceptionMessage);

        $this->tokenModel->validate();
    }

    public function testValidateIfVerifierNotValid()
    {
        $exceptionMessage = 'exceptionMessage';
        $secret = 'secret';
        $token = 'token';
        $verifier = 'isSetAndNotValid';

        $this->tokenModel->setCallbackUrl('notCallbackEstablished');
        $this->validatorMock->expects($this->once())->method('isValid')->willReturn(true);

        $this->keyLengthFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->validatorKeyLengthMock);

        $this->tokenModel->setSecret($secret);
        $this->tokenModel->setToken($token);
        $this->tokenModel->setData('verifier', $verifier);
        $this->validatorKeyLengthMock->expects($this->exactly(3))->method('isValid')->willReturnMap(
            [
                [$secret, true],
                [$token, true],
                [$verifier, false],
            ]
        );
        $this->validatorKeyLengthMock->expects($this->once())->method('getMessages')->willReturn([$exceptionMessage]);
        $this->setExpectedException(\Magento\Framework\Oauth\Exception::class, $exceptionMessage);

        $this->tokenModel->validate();
    }

    public function testValidateIfVerifierIsNotSet()
    {
        $token = 'token';
        $secret = 'secret';
        $verifier = null;

        $this->tokenModel->setCallbackUrl('notCallbackEstablished');
        $this->validatorMock->expects($this->once())->method('isValid')->willReturn(true);

        $this->keyLengthFactoryMock->expects($this->once())->method('create')->willReturn(
            $this->validatorKeyLengthMock
        );

        $this->tokenModel->setSecret($secret);
        $this->tokenModel->setToken($token);
        $this->tokenModel->setData('verifier', $verifier);
        $this->validatorKeyLengthMock->expects($this->exactly(2))->method('isValid')->willReturnMap(
            [
                [$secret, true],
                [$token, true],
            ]
        );
        $this->assertTrue($this->tokenModel->validate());
    }

    public function testValidate()
    {
        $token = 'token';
        $secret = 'secret';
        $verifier = 'verifier';

        $this->tokenModel->setCallbackUrl('notCallbackEstablished');
        $this->validatorMock->expects($this->once())->method('isValid')->willReturn(true);

        $this->keyLengthFactoryMock->expects($this->once())->method('create')->willReturn(
            $this->validatorKeyLengthMock
        );

        $this->tokenModel->setSecret($secret);
        $this->tokenModel->setToken($token);
        $this->tokenModel->setData('verifier', $verifier);
        $this->validatorKeyLengthMock->expects($this->exactly(3))->method('isValid')->willReturnMap(
            [
                [$secret, true],
                [$token, true],
                [$verifier, true],
            ]
        );
        $this->assertTrue($this->tokenModel->validate());
    }
}
