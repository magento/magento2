<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Api;

use Exception;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test class for Magento\Integration\Api\CustomerTokenServiceInterface
 */
class AccountManagementRevokeCustomerTokenTest extends WebapiAbstract
{
    public const RESOURCE_PATH = '/V1/integration/customer/revoke-customer-token';
    public const INTEGRATION_SERVICE = 'integrationCustomerTokenServiceV1';
    public const SERVICE_VERSION = 'V1';

    /**
     * Test token revoking for authenticated customer
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRevokeCustomerToken(): void
    {
        $token = $this->getCustomerToken();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token,
            ],
            'soap' => [
                'service' => self::INTEGRATION_SERVICE,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::INTEGRATION_SERVICE . 'RevokeCustomerAccessToken',
                'token' => $token,
            ]
        ];

        $requestData = [];
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData['customerId'] = 0;
        }

        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @return string
     *
     * @throws AuthenticationException
     */
    private function getCustomerToken(): string
    {
        $userName = 'customer@example.com';
        $password = 'password';

        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()->get(CustomerTokenServiceInterface::class);

        return $customerTokenService->createCustomerAccessToken($userName, $password);
    }

    /**
     * Test token revoking for guest customer
     */
    public function testRevokeCustomerTokenForGuestCustomer(): void
    {
        $this->expectException(Exception::class);
        $requestData = [];

        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData['customerId'] = 0;
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::INTEGRATION_SERVICE,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::INTEGRATION_SERVICE . 'RevokeCustomerAccessToken',
            ]
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }
}
