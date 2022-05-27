<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Plugin\Block\Adminhtml;

use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdminAdobeIms\Plugin\Block\Adminhtml\SignInPlugin;
use Magento\AdobeIms\Block\Adminhtml\SignIn;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\ConfigProviderInterface;
use Magento\AdobeImsApi\Api\UserAuthorizedInterface;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test plugin that retrieves authentication component configuration if Admin Adobe IMS is enabled
 */
class SignInPluginTest extends TestCase
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
     * @var UserAuthorizedInterface|MockObject
     */
    private $userAuthorizedMock;

    /**
     * @var JsonHexTag|MockObject
     */
    private $serializer;

    /**
     * @var SignInPlugin;
     */
    private $signInPlugin;

    /**
     * @var ImsConfig|MockObject
     */
    private ImsConfig $adminAdobeImsConfig;

    /**
     * @var Auth|MockObject
     */
    private Auth $auth;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->expects($this->once())
            ->method('getAuthUrl')
            ->willReturn(self::AUTH_URL);

        $this->userAuthorizedMock = $this->createMock(UserAuthorizedInterface::class);
        $this->serializer = $this->createMock(JsonHexTag::class);
        $this->adminAdobeImsConfig = $this->createMock(ImsConfig::class);
        $this->auth = $this->createMock(Auth::class);

        $objectManager = new ObjectManager($this);
        $this->signInPlugin = $objectManager->getObject(
            SignInPlugin::class,
            [
                'adminAdobeImsConfig' => $this->adminAdobeImsConfig,
                'auth' => $this->auth,
                'userAuthorized' => $this->userAuthorizedMock,
                'serializer' => $this->serializer,
                'config' => $configMock
            ]
        );
    }

    /**
     * @dataProvider userDataProvider
     * @param array $userData
     * @param array $configProviderData
     * @param array $expectedData
     * @param bool $isAuthorized
     */
    public function testAroundGetComponentJsonConfig(
        array $userData,
        array $configProviderData,
        array $expectedData,
        bool $isAuthorized
    ): void {
        $this->userAuthorizedMock->expects($this->once())
            ->method('execute')
            ->willReturn($userData['isAuthorized']);

        $userProfile = $this->createMock(User::class);
        if ($isAuthorized) {
            $userProfile->method('getName')->willReturn($userData['name']);
            $userProfile->method('getEmail')->willReturn($userData['email']);
        }

        $this->adminAdobeImsConfig->method('enabled')->willReturn(true);
        $this->auth->method('getUser')->willReturn($userProfile);

        $subject = $this->createMock(SignIn::class);
        $configProviderMock = $this->createMock(ConfigProviderInterface::class);
        $configProviderMock->method('get')->willReturn($configProviderData);
        $subject->method('getData')->willReturn($configProviderMock);
        $subject->method('getUrl')->willReturn(self::PROFILE_URL);

        $serializedResult = 'Some result';
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($expectedData)
            ->willReturn($serializedResult);

        $closure = function () {
            return $this->createMock(SignIn::class);
        };

        $this->assertEquals($serializedResult, $this->signInPlugin->aroundGetComponentJsonConfig($subject, $closure));
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
            'isGlobalSignInEnabled' => true,
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
     * @return array
     */
    public function userDataProvider(): array
    {
        return [
            'Existing authorized user' => [
                [
                    'isAuthorized' => true,
                    'name' => 'John Doe',
                    'email' => 'john@email.com',
                ],
                [],
                $this->getDefaultComponentConfig([
                    'isAuthorized' => true,
                    'name' => 'John Doe',
                    'email' => 'john@email.com',
                    'image' => ''
                ]),
                true
            ],
            'Existing non-authorized user' => [
                [
                    'isAuthorized' => false,
                    'name' => 'John Doe',
                    'email' => 'john@email.com',
                    'image' => 'image.png'
                ],
                [],
                $this->getDefaultComponentConfig($this->getDefaultUserData()),
                false
            ],
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
}
