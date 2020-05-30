<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Framework\Exception\InputException;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Model\User as UserModel;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;
use Magento\Integration\Model\Oauth\Token\RequestLog\Config as TokenThrottlerConfig;

/**
 * api-functional test for \Magento\Integration\Model\AdminTokenService.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminTokenServiceTest extends WebapiAbstract
{
    const SERVICE_NAME = "integrationAdminTokenServiceV1";
    const SERVICE_VERSION = "V1";
    const RESOURCE_PATH_ADMIN_TOKEN = "/V1/integration/admin/token";

    /**
     * @var \Magento\Integration\Api\AdminTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     * @var UserModel
     */
    private $userModel;

    /**
     * @var int
     */
    private $attemptsCountToLockAccount;

    /**
     * Setup AdminTokenService
     */
    protected function setUp(): void
    {
        $this->markTestSkipped('Skipped until MC-34201 is addressed');
        $this->_markTestAsRestOnly();
        $this->tokenService = Bootstrap::getObjectManager()->get(\Magento\Integration\Model\AdminTokenService::class);
        $this->tokenModel = Bootstrap::getObjectManager()->get(\Magento\Integration\Model\Oauth\Token::class);
        $this->userModel = Bootstrap::getObjectManager()->get(\Magento\User\Model\User::class);
        /** @var TokenThrottlerConfig $tokenThrottlerConfig */
        $tokenThrottlerConfig = Bootstrap::getObjectManager()->get(TokenThrottlerConfig::class);
        $this->attemptsCountToLockAccount = $tokenThrottlerConfig->getMaxFailuresCount();
    }

    /**
     * @magentoApiDataFixture Magento/Webapi/_files/webapi_user.php
     */
    public function testCreateAdminAccessToken()
    {
        $adminUserNameFromFixture = 'webapi_user';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_ADMIN_TOKEN,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = [
            'username' => $adminUserNameFromFixture,
            'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
        ];
        $accessToken = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertToken($adminUserNameFromFixture, $accessToken);
    }

    /**
     * Provider to test input validation
     *
     * @return array
     */
    public function validationDataProvider()
    {
        return [
            'Check for empty credentials' => ['', ''],
            'Check for null credentials' => [null, null]
        ];
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function testCreateAdminAccessTokenEmptyOrNullCredentials()
    {
        $noExceptionOccurred = false;
        try {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH_ADMIN_TOKEN,
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                ],
            ];
            $requestData = ['username' => '', 'password' => ''];
            $this->_webApiCall($serviceInfo, $requestData);
            $noExceptionOccurred = true;
        } catch (\Exception $exception) {
            $this->assertInputExceptionMessages($exception);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when provided credentials are invalid.");
        }
    }

    public function testCreateAdminAccessTokenInvalidCredentials()
    {
        $customerUserName = 'invalid';
        $password = 'invalid';
        $noExceptionOccurred = false;
        try {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH_ADMIN_TOKEN,
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                ],
            ];
            $requestData = ['username' => $customerUserName, 'password' => $password];
            $this->_webApiCall($serviceInfo, $requestData);
            $noExceptionOccurred = true;
        } catch (\Exception $exception) {
            $this->assertInvalidCredentialsException($exception);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when provided credentials are invalid.");
        }
    }

    /**
     * @magentoApiDataFixture Magento/Webapi/_files/webapi_user.php
     */
    public function testUseAdminAccessTokenInactiveAdmin()
    {
        $adminUserNameFromFixture = 'webapi_user';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_ADMIN_TOKEN,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = [
            'username' => $adminUserNameFromFixture,
            'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
        ];
        $accessToken = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertToken($adminUserNameFromFixture, $accessToken);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/store/storeConfigs',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $accessToken
            ]
        ];
        $requestData = [
            'storeCodes' => ['default'],
        ];
        $storeConfigs = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($storeConfigs);

        $adminUser = $this->userModel->loadByUsername($adminUserNameFromFixture);
        $adminUser->setData("is_active", 0);
        $adminUser->save();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/store/storeConfigs',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $accessToken
            ]
        ];
        $requestData = [
            'storeCodes' => ['default'],
        ];

        $noExceptionOccurred = false;
        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $noExceptionOccurred = true;
        } catch (\Exception $exception) {
            $this->assertUnauthorizedAccessException($exception);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when provided token is expired.");
        }
    }

    /**
     * @magentoApiDataFixture Magento/Webapi/_files/webapi_user.php
     */
    public function testThrottlingMaxAttempts()
    {
        $adminUserNameFromFixture = 'webapi_user';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_ADMIN_TOKEN,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $invalidCredentials = [
            'username' => $adminUserNameFromFixture,
            'password' => 'invalid',
        ];
        $validCredentials = [
            'username' => $adminUserNameFromFixture,
            'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
        ];

        /* Try to get token using invalid credentials for 5 times (account is locked after 6 attempts) */
        $noExceptionOccurred = false;
        for ($i = 0; $i < ($this->attemptsCountToLockAccount - 1); $i++) {
            try {
                $this->_webApiCall($serviceInfo, $invalidCredentials);
                $noExceptionOccurred = true;
            } catch (\Exception $exception) {
            }
        }
        if ($noExceptionOccurred) {
            $this->fail(
                "Precondition failed: exception should have occurred when token was requested with invalid credentials."
            );
        }

        /** On 6th attempt it still should be possible to get token if valid credentials are specified */
        $accessToken = $this->_webApiCall($serviceInfo, $validCredentials);
        $this->assertToken($adminUserNameFromFixture, $accessToken);
    }

    /**
     * @magentoApiDataFixture Magento/Webapi/_files/webapi_user.php
     */
    public function testThrottlingAccountLockout()
    {
        $adminUserNameFromFixture = 'webapi_user';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_ADMIN_TOKEN,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $invalidCredentials = [
            'username' => $adminUserNameFromFixture,
            'password' => 'invalid',
        ];
        $validCredentials = [
            'username' => $adminUserNameFromFixture,
            'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
        ];

        /* Try to get token using invalid credentials for 5 times (account would be locked after 6 attempts) */
        $noExceptionOccurred = false;
        for ($i = 0; $i < $this->attemptsCountToLockAccount; $i++) {
            try {
                $this->_webApiCall($serviceInfo, $invalidCredentials);
                $noExceptionOccurred = true;
            } catch (\Exception $exception) {
                $this->assertInvalidCredentialsException($exception);
            }
            if ($noExceptionOccurred) {
                $this->fail("Exception was expected to be thrown when provided credentials are invalid.");
            }
        }

        $noExceptionOccurred = false;
        try {
            $this->_webApiCall($serviceInfo, $validCredentials);
            $noExceptionOccurred = true;
        } catch (\Exception $exception) {
            $this->assertInvalidCredentialsException($exception);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown because account should have been locked at this point.");
        }
    }

    /**
     * Assert for presence of Input exception messages
     *
     * @param \Exception $exception
     */
    private function assertInputExceptionMessages($exception)
    {
        $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $exception->getCode());
        $exceptionData = $this->processRestExceptionResult($exception);
        $expectedExceptionData = [
            'message' => 'One or more input exceptions have occurred.',
            'errors' => [
                [
                    'message' => '"%fieldName" is required. Enter and try again.',
                    'parameters' => [
                        'fieldName' => 'username',
                    ],
                ],
                [
                    'message' => '"%fieldName" is required. Enter and try again.',
                    'parameters' => [
                        'fieldName' => 'password',
                    ]
                ],
            ],
        ];
        $this->assertEquals($expectedExceptionData, $exceptionData);
    }

    /**
     * Make sure that status code and message are correct in case of authentication failure.
     *
     * @param \Exception $exception
     */
    private function assertInvalidCredentialsException($exception)
    {
        $this->assertEquals(
            HTTPExceptionCodes::HTTP_UNAUTHORIZED,
            $exception->getCode(),
            "Response HTTP code is invalid."
        );
        $exceptionData = $this->processRestExceptionResult($exception);
        $expectedExceptionData = [
            'message' => 'The account sign-in was incorrect or your account is disabled temporarily. '
                . 'Please wait and try again later.'
        ];
        $this->assertEquals($expectedExceptionData, $exceptionData, "Exception message is invalid.");
    }

    /**
     * Make sure that status code and message are correct in case of authentication failure.
     *
     * @param \Exception $exception
     */
    private function assertUnauthorizedAccessException($exception)
    {
        $this->assertEquals(
            HTTPExceptionCodes::HTTP_UNAUTHORIZED,
            $exception->getCode(),
            "Response HTTP code is invalid."
        );
        $exceptionData = $this->processRestExceptionResult($exception);
        $expectedExceptionData = [
            'message' => "The consumer isn't authorized to access %resources.",
            'parameters' => [
                'resources' => 'Magento_Backend::store'
            ]
        ];
        $this->assertEquals($expectedExceptionData, $exceptionData, "Exception message is invalid.");
    }

    /**
     * Make sure provided token is valid and belongs to the specified user.
     *
     * @param string $username
     * @param string $accessToken
     */
    private function assertToken($username, $accessToken)
    {
        $adminUserId = $this->userModel->loadByUsername($username)->getId();
        /** @var $token TokenModel */
        $token = $this->tokenModel
            ->loadByAdminId($adminUserId)
            ->getToken();
        $this->assertEquals($accessToken, $token);
    }
}
