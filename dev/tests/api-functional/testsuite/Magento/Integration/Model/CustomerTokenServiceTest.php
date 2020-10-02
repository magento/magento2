<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Model\User as UserModel;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory;
use Magento\Integration\Model\Oauth\Token\RequestLog\Config as TokenThrottlerConfig;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * api-functional test for \Magento\Integration\Model\CustomerTokenService.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerTokenServiceTest extends WebapiAbstract
{
    const SERVICE_NAME = "integrationCustomerTokenServiceV1";
    const SERVICE_VERSION = "V1";
    const RESOURCE_PATH_CUSTOMER_TOKEN = "/V1/integration/customer/token";

    /**
     * @var CustomerTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var CollectionFactory
     */
    private $tokenCollection;

    /**
     * @var UserModel
     */
    private $userModel;

    /**
     * @var int
     */
    private $attemptsCountToLockAccount;

    /**
     * Setup CustomerTokenService
     */
    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $this->tokenService = Bootstrap::getObjectManager()->get(
            \Magento\Integration\Model\CustomerTokenService::class
        );
        $this->customerAccountManagement = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        $tokenCollectionFactory = Bootstrap::getObjectManager()->get(
            \Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory::class
        );
        $this->tokenCollection = $tokenCollectionFactory->create();
        $this->userModel = Bootstrap::getObjectManager()->get(\Magento\User\Model\User::class);
        /** @var TokenThrottlerConfig $tokenThrottlerConfig */
        $tokenThrottlerConfig = Bootstrap::getObjectManager()->get(TokenThrottlerConfig::class);
        $this->attemptsCountToLockAccount = $tokenThrottlerConfig->getMaxFailuresCount();
    }

    /**
     * Create customer access token
     *
     * @dataProvider storesDataProvider
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @param string|null $store
     * @return void
     */
    public function testCreateCustomerAccessToken(?string $store): void
    {
        $userName = 'customer@example.com';
        $password = 'password';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = ['username' => $userName, 'password' => $password];
        $accessToken = $this->_webApiCall($serviceInfo, $requestData, null, $store);

        $this->assertToken($accessToken, $userName, $password);
    }

    /**
     * DataProvider for testCreateCustomerAccessToken
     *
     * @return array
     */
    public function storesDataProvider(): array
    {
        return [
            'default store' => [null],
            'all store view' => ['all'],
        ];
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function testCreateCustomerAccessTokenEmptyOrNullCredentials($username, $password)
    {
        $noExceptionOccurred = false;
        try {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ],
            ];
            $requestData = ['username' => $username, 'password' => $password];
            $this->_webApiCall($serviceInfo, $requestData);
            $noExceptionOccurred = true;
        } catch (\Exception $e) {
            $this->assertInputExceptionMessages($e);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when provided credentials are invalid.");
        }
    }

    public function testCreateCustomerAccessTokenInvalidCustomer()
    {
        $customerUserName = 'invalid';
        $password = 'invalid';
        $noExceptionOccurred = false;
        try {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ],
            ];
            $requestData = ['username' => $customerUserName, 'password' => $password];
            $this->_webApiCall($serviceInfo, $requestData);
            $noExceptionOccurred = true;
        } catch (\Exception $e) {
            $this->assertInvalidCredentialsException($e);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when provided credentials are invalid.");
        }
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
     * Assert for presence of Input exception messages
     *
     * @param \Exception $e
     */
    private function assertInputExceptionMessages($e)
    {
        $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
        $exceptionData = $this->processRestExceptionResult($e);
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
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testThrottlingMaxAttempts()
    {
        $userNameFromFixture = 'customer@example.com';
        $passwordFromFixture = 'password';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $invalidCredentials = [
            'username' => $userNameFromFixture,
            'password' => 'invalid',
        ];
        $validCredentials = [
            'username' => $userNameFromFixture,
            'password' => $passwordFromFixture,
        ];

        /* Try to get token using invalid credentials for 5 times (account is locked after 6 attempts) */
        $noExceptionOccurred = false;
        for ($i = 0; $i < ($this->attemptsCountToLockAccount - 1); $i++) {
            try {
                $this->_webApiCall($serviceInfo, $invalidCredentials);
                $noExceptionOccurred = true;
            } catch (\Exception $e) {
            }
        }
        if ($noExceptionOccurred) {
            $this->fail(
                "Precondition failed: exception should have occurred when token was requested with invalid credentials."
            );
        }

        /** On 6th attempt it still should be possible to get token if valid credentials are specified */
        $accessToken = $this->_webApiCall($serviceInfo, $validCredentials);
        $this->assertToken($accessToken, $userNameFromFixture, $passwordFromFixture);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testThrottlingAccountLockout()
    {
        $userNameFromFixture = 'customer@example.com';
        $passwordFromFixture = 'password';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $invalidCredentials = [
            'username' => $userNameFromFixture,
            'password' => 'invalid',
        ];
        $validCredentials = [
            'username' => $userNameFromFixture,
            'password' => $passwordFromFixture,
        ];

        /* Try to get token using invalid credentials for 5 times (account would be locked after 6 attempts) */
        $noExceptionOccurred = false;
        for ($i = 0; $i < $this->attemptsCountToLockAccount; $i++) {
            try {
                $this->_webApiCall($serviceInfo, $invalidCredentials);
                $noExceptionOccurred = true;
            } catch (\Exception $e) {
                $this->assertInvalidCredentialsException($e);
            }
            if ($noExceptionOccurred) {
                $this->fail("Exception was expected to be thrown when provided credentials are invalid.");
            }
        }

        $noExceptionOccurred = false;
        try {
            $this->_webApiCall($serviceInfo, $validCredentials);
            $noExceptionOccurred = true;
        } catch (\Exception $e) {
            $this->assertInvalidCredentialsException($e);
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown because account should have been locked at this point.");
        }
    }

    /**
     * Make sure that status code and message are correct in case of authentication failure.
     *
     * @param \Exception $e
     */
    private function assertInvalidCredentialsException($e)
    {
        $this->assertEquals(HTTPExceptionCodes::HTTP_UNAUTHORIZED, $e->getCode(), "Response HTTP code is invalid.");
        $exceptionData = $this->processRestExceptionResult($e);
        $expectedExceptionData = [
            'message' => 'The account sign-in was incorrect or your account is disabled temporarily. '
                . 'Please wait and try again later.'
        ];
        $this->assertEquals($expectedExceptionData, $exceptionData, "Exception message is invalid.");
    }

    /**
     * Make sure provided token is valid and belongs to the specified user.
     *
     * @param string $accessToken
     * @param string $userName
     * @param string $password
     */
    private function assertToken($accessToken, $userName, $password)
    {
        $customerData = $this->customerAccountManagement->authenticate($userName, $password);
        /** @var $this ->tokenCollection \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection */
        $this->tokenCollection->addFilterByCustomerId($customerData->getId());
        $isTokenCorrect = false;
        foreach ($this->tokenCollection->getItems() as $item) {
            /** @var $item TokenModel */
            if ($item->getToken() == $accessToken) {
                $isTokenCorrect = true;
            }
        }
        $this->assertTrue($isTokenCorrect);
    }
}
