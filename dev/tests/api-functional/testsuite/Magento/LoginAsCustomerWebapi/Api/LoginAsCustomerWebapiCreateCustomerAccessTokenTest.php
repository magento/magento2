<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerWebapi\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\Integration\Model\ResourceModel\Oauth\Token\Collection;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory;
use Magento\TestFramework\Authentication\OauthHelper;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Api-functional test for \Magento\LoginAsCustomerWebapi\Api\LoginAsCustomerWebapiCreateCustomerAccessTokenInterface.
 */
class LoginAsCustomerWebapiCreateCustomerAccessTokenTest extends WebapiAbstract
{
    const RESOURCE_PATH = "/V1/login-as-customer/token";

    /**
     * @var Collection
     */
    private $tokenCollection;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $tokenCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
        $this->tokenCollection = $tokenCollectionFactory->create();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture login_as_customer/general/enabled 1
     */
    public function testCreateCustomerAccessToken()
    {
        // 'Magento_LoginAsCustomerWebapi::login_token' resource required for access.
        OauthHelper::clearApiAccessCredentials();
        OauthHelper::getApiAccessCredentials(['Magento_LoginAsCustomerWebapi::login_token']);
        try {
            $customerId = 1;

            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH,
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ],
            ];
            $requestData = ['customerId' => $customerId];
            $response = $this->_webApiCall($serviceInfo, $requestData);

            $this->assertToken($response, $customerId);
        } catch (\Exception $e) {
            OauthHelper::clearApiAccessCredentials();
            throw $e;
        }
        // Restore credentials
        OauthHelper::clearApiAccessCredentials();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture login_as_customer/general/enabled 0
     */
    public function testCreateCustomerAccessTokenLoginModuleDisabled()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Service is disabled.');

        $customerId = 1;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = ['customerId' => $customerId];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture login_as_customer/general/enabled 1
     */
    public function testCreateCustomerAccessTokenLoginNoAccess()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The consumer isn\'t authorized to access %resources.');

        // 'Magento_LoginAsCustomerWebapi::login_token' resource required for access.
        OauthHelper::clearApiAccessCredentials();
        OauthHelper::getApiAccessCredentials([]);
        try {
            $customerId = 1;

            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH,
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ],
            ];
            $requestData = ['customerId' => $customerId];
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\Exception $e) {
            OauthHelper::clearApiAccessCredentials();
            throw $e;
        }
        // Restore credentials
        OauthHelper::clearApiAccessCredentials();
    }

    /**
     * Make sure provided token is valid and belongs to the specified user.
     *
     * @param string $response
     * @param int $customerId
     */
    private function assertToken(string $response, int $customerId)
    {
        $this->tokenCollection->addFilterByCustomerId($customerId);
        $isTokenCorrect = false;
        foreach ($this->tokenCollection->getItems() as $item) {
            /** @var $item TokenModel */
            if ($item->getToken() == $response) {
                $isTokenCorrect = true;
            }
        }

        $this->assertTrue($isTokenCorrect);
    }
}
