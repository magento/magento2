<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Webapi\Routing;

use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * API-functional coverage for GitHub issue #31551: malformed request body/params
 * must return client 4xx responses, never HTTP 500.
 *
 * @see https://github.com/magento/magento2/issues/31551
 */
class MalformedRequestErrorHandlingTest extends WebapiAbstract
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        parent::setUp();
    }

    /**
     * Scalar body for a complex type on PUT /V1/customers/me must yield HTTP 400.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testPutCustomersMeWithScalarCustomerBodyReturns400(): void
    {
        $token = Bootstrap::getObjectManager()
            ->get(CustomerTokenServiceInterface::class)
            ->createCustomerAccessToken('customer@example.com', 'password');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/customers/me',
                'httpMethod' => Request::HTTP_METHOD_PUT,
                'token' => $token,
            ],
        ];

        $this->assertClientErrorHttpCode(
            function () use ($serviceInfo) {
                $this->_webApiCall($serviceInfo, ['customer' => '']);
            },
            WebapiException::HTTP_BAD_REQUEST
        );
    }

    /**
     * Scalar values for complex product/options on variation endpoint must yield HTTP 400.
     *
     * Uses default OAuth integration credentials (admin-level Magento_Catalog::products).
     */
    public function testPutConfigurableProductVariationWithScalarBodyReturns400(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/configurable-products/variation',
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
        ];

        $this->assertClientErrorHttpCode(
            function () use ($serviceInfo) {
                $this->_webApiCall(
                    $serviceInfo,
                    [
                        'options' => '',
                        'product' => '',
                    ]
                );
            },
            WebapiException::HTTP_BAD_REQUEST
        );
    }

    /**
     * Unknown search requestName must yield HTTP 404 (NonExistingRequestNameException).
     *
     * Route is anonymous per Magento_Search webapi.xml.
     */
    public function testGetSearchWithInvalidRequestNameReturns404(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/search?searchCriteria[requestName]=1',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];

        $this->assertClientErrorHttpCode(
            function () use ($serviceInfo) {
                $this->_webApiCall($serviceInfo);
            },
            WebapiException::HTTP_NOT_FOUND
        );
    }

    /**
     * Numeric-only EAV filter field on customer search must yield HTTP 400 (not 500).
     *
     * Uses default OAuth integration credentials (Magento_Customer::customer).
     */
    public function testGetCustomersSearchWithNumericFilterFieldReturns400(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/customers/search?'
                    . 'searchCriteria[filterGroups][0][filters][0][field]=1',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];

        $this->assertClientErrorHttpCode(
            function () use ($serviceInfo) {
                $this->_webApiCall($serviceInfo);
            },
            WebapiException::HTTP_BAD_REQUEST
        );
    }

    /**
     * Empty resetPassword inputs must yield HTTP 400 via InputException.
     */
    public function testPostResetPasswordWithEmptyStringsReturns400(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/customers/resetPassword',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];

        $this->assertClientErrorHttpCode(
            function () use ($serviceInfo) {
                $this->_webApiCall(
                    $serviceInfo,
                    [
                        'email' => '',
                        'resetToken' => '',
                        'newPassword' => '',
                    ]
                );
            },
            WebapiException::HTTP_BAD_REQUEST
        );
    }

    /**
     * Invoke a Web API call and assert the HTTP status on the thrown exception.
     *
     * Explicitly asserts the code is not 500 so a regression to Internal Server Error fails loudly.
     *
     * @param callable $webApiCall
     * @param int $expectedHttpCode
     * @return void
     */
    private function assertClientErrorHttpCode(callable $webApiCall, int $expectedHttpCode): void
    {
        try {
            $webApiCall();
            $this->fail(
                sprintf('Expected HTTP %d exception was not thrown.', $expectedHttpCode)
            );
        } catch (\Exception $e) {
            $this->assertNotEquals(
                WebapiException::HTTP_INTERNAL_ERROR,
                $e->getCode(),
                'Malformed request must not produce HTTP 500'
            );
            $this->assertEquals(
                $expectedHttpCode,
                $e->getCode(),
                sprintf('Expected HTTP %d, got %d', $expectedHttpCode, $e->getCode())
            );
        }
    }
}
