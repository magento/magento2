<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Block\Adminhtml;

use Magento\AdobeIms\Block\Adminhtml\SignIn as SignInBlock;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\ConfigProviderInterface;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\UserAuthorizedInterface;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Config data test.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SignInTest extends TestCase
{
    private const PROFILE_URL = 'https://url.test/';
    private const LOGOUT_URL = 'https://url.test/';
    private const AUTH_URL = '';
    private const RESPONSE_REGEXP_PATTERN = 'auth\\[code=(success|error);message=(.+)\\]';
    private const RESPONSE_CODE_INDEX = 1;
    private const RESPONSE_MESSAGE_INDEX = 2;
    private const RESPONSE_SUCCESS_CODE = 'success';
    private const RESPONSE_ERROR_CODE = 'error';

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContextMock;

    /**
     * @var UserAuthorizedInterface|MockObject
     */
    private $userAuthorizedMock;

    /**
     * @var UserProfileRepositoryInterface|MockObject
     */
    private $userProfileRepositoryMock;

    /**
     * @var JsonHexTag|MockObject
     */
    private $jsonHexTag;

    /**
     * @var SignInBlock;
     */
    private $signInBlock;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->expects($this->once())
            ->method('getAuthUrl')
            ->willReturn(self::AUTH_URL);

        $urlBuilderMock = $this->createMock(UrlInterface::class);
        $urlBuilderMock->method('getUrl')
            ->willReturn(self::PROFILE_URL);
        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getUrlBuilder')
            ->willReturn($urlBuilderMock);

        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->userAuthorizedMock = $this->createMock(UserAuthorizedInterface::class);
        $this->userProfileRepositoryMock = $this->createMock(UserProfileRepositoryInterface::class);
        $this->jsonHexTag = $this->createMock(JsonHexTag::class);

        $objectManager = new ObjectManager($this);
        $this->signInBlock = $objectManager->getObject(
            SignInBlock::class,
            [
                'config' => $configMock,
                'context' => $contextMock,
                'userContext' => $this->userContextMock,
                'userAuthorized' => $this->userAuthorizedMock,
                'userProfileRepository' => $this->userProfileRepositoryMock,
                'json' => $this->jsonHexTag
            ]
        );
    }

    /**
     * @dataProvider userDataProvider
     * @param int $userId
     * @param bool $userExists
     * @param array $userData
     * @param array $configProviderData
     * @param array $expectedData
     */
    public function testGetComponentJsonConfig(
        int $userId,
        bool $userExists,
        array $userData,
        array $configProviderData,
        array $expectedData
    ): void {
        $this->userAuthorizedMock->expects($this->once())
            ->method('execute')
            ->willReturn($userData['isAuthorized']);

        $userProfile = $this->createMock(UserProfileInterface::class);
        $userProfile->method('getName')->willReturn($userData['name']);
        $userProfile->method('getEmail')->willReturn($userData['email']);
        $userProfile->method('getImage')->willReturn($userData['image']);

        $this->userContextMock->expects($this->any())
            ->method('getUserId')
            ->willReturn($userId);

        $userRepositoryWillReturn = $userExists
            ? $this->returnValue($userProfile)
            : $this->throwException(new NoSuchEntityException());
        $this->userProfileRepositoryMock
            ->method('getByUserId')
            ->with($userId)
            ->will($userRepositoryWillReturn);

        $configProviderMock = $this->createMock(ConfigProviderInterface::class);
        $configProviderMock->expects($this->any())
            ->method('get')
            ->willReturn($configProviderData);
        $this->signInBlock->setData('configProviders', [$configProviderMock]);

        $serializedResult = 'Some result';
        $this->jsonHexTag->expects($this->once())
            ->method('serialize')
            ->with($expectedData)
            ->willReturn($serializedResult);

        $this->assertEquals($serializedResult, $this->signInBlock->getComponentJsonConfig());
    }

    /**
     * Returns default component config
     *
     * @param array $userData
     * @return array
     */
    private function getDefaultComponentConfig(array $userData): array
    {
        return [
            'component' => 'Magento_AdobeIms/js/signIn',
            'template' => 'Magento_AdobeIms/signIn',
            'profileUrl' => self::PROFILE_URL,
            'logoutUrl' => self::LOGOUT_URL,
            'user' => $userData,
            'isGlobalSignInEnabled' => false,
            'loginConfig' => [
                'url' => self::AUTH_URL,
                'callbackParsingParams' => [
                    'regexpPattern' => self::RESPONSE_REGEXP_PATTERN,
                    'codeIndex' => self::RESPONSE_CODE_INDEX,
                    'messageIndex' => self::RESPONSE_MESSAGE_INDEX,
                    'successCode' => self::RESPONSE_SUCCESS_CODE,
                    'errorCode' => self::RESPONSE_ERROR_CODE
                ]
            ]
        ];
    }

    /**
     * Returns config from an additional config provider
     *
     * @return array
     */
    private function getConfigProvideConfig(): array
    {
        return [
            'component' => 'Magento_AdobeIms/js/test',
            'template' => 'Magento_AdobeIms/test',
            'profileUrl' => '',
            'logoutUrl' => '',
            'user' => [],
            'loginConfig' => [
                'url' => 'https://sometesturl.test',
                'callbackParsingParams' => [
                    'regexpPattern' => self::RESPONSE_REGEXP_PATTERN,
                    'codeIndex' => self::RESPONSE_CODE_INDEX,
                    'messageIndex' => self::RESPONSE_MESSAGE_INDEX,
                    'successCode' => self::RESPONSE_SUCCESS_CODE,
                    'errorCode' => self::RESPONSE_ERROR_CODE
                ]
            ]
        ];
    }

    /**
     * Get default user data for an assertion
     *
     * @return array
     */
    private function getDefaultUserData(): array
    {
        return [
            'isAuthorized' => false,
            'name' => '',
            'email' => '',
            'image' => '',
        ];
    }

    /**
     * @return array
     */
    public function userDataProvider(): array
    {
        return [
            'Existing authorized user' => [
                11,
                true,
                [
                    'isAuthorized' => true,
                    'name' => 'John',
                    'email' => 'john@email.com',
                    'image' => 'image.png'
                ],
                [],
                $this->getDefaultComponentConfig([
                    'isAuthorized' => true,
                    'name' => 'John',
                    'email' => 'john@email.com',
                    'image' => 'image.png'
                ])
            ],
            'Existing non-authorized user' => [
                12,
                true,
                [
                    'isAuthorized' => false,
                    'name' => 'John',
                    'email' => 'john@email.com',
                    'image' => 'image.png'
                ],
                [],
                $this->getDefaultComponentConfig($this->getDefaultUserData()),
            ],
            'Non-existing user' => [
                13,
                false, //user doesn't exist
                [
                    'isAuthorized' => true,
                    'name' => 'John',
                    'email' => 'john@email.com',
                    'image' => 'image.png'
                ],
                [],
                $this->getDefaultComponentConfig($this->getDefaultUserData()),
            ],
            'Existing user with additional config provider' => [
                14,
                true,
                [
                    'isAuthorized' => false,
                    'name' => 'John',
                    'email' => 'john@email.com',
                    'image' => 'image.png'
                ],
                $this->getConfigProvideConfig(),
                array_replace_recursive(
                    $this->getDefaultComponentConfig($this->getDefaultUserData()),
                    $this->getConfigProvideConfig()
                )
            ]
        ];
    }
}
