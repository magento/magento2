<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * @magentoApiDataFixture Magento/Customer/_files/customer.php
 * @magentoApiDataFixture Magento/Store/_files/second_website_with_two_stores.php
 */
class CustomerSharingOptionsTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/customers/me';
    const REPO_SERVICE = 'customerCustomerRepositoryV1';
    const SERVICE_VERSION = 'V1';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var TokenModel
     */
    private $token;

    /**
     * @var CustomerInterface
     */
    private $customerData;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $tokenService;

    /**
     * Execute per test initialization.
     */
    public function setUp(): void
    {
        $this->customerRegistry = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Model\CustomerRegistry::class
        );

        $this->customerRepository = Bootstrap::getObjectManager()->get(
            CustomerRepositoryInterface::class,
            ['customerRegistry' => $this->customerRegistry]
        );

        $this->customerHelper = new CustomerHelper();
        $this->customerData = $this->customerHelper->createSampleCustomer();
        $this->tokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);

        // get token
        $this->resetTokenForCustomerSampleData();
    }

    /**
     * Ensure that fixture customer and his addresses are deleted.
     */
    public function tearDown(): void
    {
        $this->customerRepository = null;

        /** @var Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
        parent::tearDown();
    }

    /**
     * @param string $storeCode
     * @param bool $expectingException
     * @dataProvider getCustomerDataWebsiteScopeDataProvider
     *
     * @magentoConfigFixture default_store customer/account_share/scope 1
     */
    public function testGetCustomerDataWebsiteScope(string $storeCode, bool $expectingException)
    {
        $this->_markTestAsRestOnly('SOAP is difficult to generate exception messages, inconsistencies in WSDL');
        $this->processGetCustomerData($storeCode, $expectingException);
    }

    /**
     * @param string $storeCode
     * @param bool $expectingException
     * @dataProvider getCustomerDataGlobalScopeDataProvider
     *
     * @magentoConfigFixture customer/account_share/scope 0
     */
    public function testGetCustomerDataGlobalScope(string $storeCode, bool $expectingException)
    {
        $this->processGetCustomerData($storeCode, $expectingException);
    }

    /**
     * @param string $storeCode
     * @param bool $expectingException
     */
    private function processGetCustomerData(string $storeCode, bool $expectingException)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $this->token,
            ],
            'soap' => [
                'service' => self::REPO_SERVICE,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::REPO_SERVICE . 'GetSelf',
                'token' => $this->token
            ]
        ];
        $arguments = [];
        if (TESTS_WEB_API_ADAPTER === 'soap') {
            $arguments['customerId'] = 0;
        }

        if ($expectingException) {
            self::expectException(\Exception::class);
            self::expectExceptionMessage("The consumer isn't authorized to access %resources.");
        }

        $this->_webApiCall($serviceInfo, $arguments, null, $storeCode);
    }

    /**
     * Data provider for testGetCustomerDataWebsiteScope.
     *
     * @return array
     */
    public function getCustomerDataWebsiteScopeDataProvider(): array
    {
        return [
            'Default Store View' => [
                'store_code' => 'default',
                'exception' => false
            ],
            'Custom Store View' => [
                'store_code' => 'fixture_second_store',
                'exception' => true
            ]
        ];
    }

    /**
     * Data provider for testGetCustomerDataGlobalScope.
     *
     * @return array
     */
    public function getCustomerDataGlobalScopeDataProvider(): array
    {
        return [
            'Default Store View' => [
                'store_code' => 'default',
                'exception' => false
            ],
            'Custom Store View' => [
                'store_code' => 'fixture_second_store',
                'exception' => false
            ]
        ];
    }

    /**
     * Sets the test's access token for the created customer sample data
     */
    private function resetTokenForCustomerSampleData()
    {
        $this->resetTokenForCustomer($this->customerData[CustomerInterface::EMAIL], 'test@123');
    }

    /**
     * Sets the test's access token for a particular username and password.
     *
     * @param string $username
     * @param string $password
     */
    private function resetTokenForCustomer($username, $password)
    {
        $this->token = $this->tokenService->createCustomerAccessToken($username, $password);
        $this->customerRegistry->remove($this->customerRepository->get($username)->getId());
    }
}
