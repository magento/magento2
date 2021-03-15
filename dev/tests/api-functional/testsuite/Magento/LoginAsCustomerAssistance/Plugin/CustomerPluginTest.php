<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface as Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Webapi\Rest\Request;
use Magento\LoginAsCustomerAssistance\Api\IsAssistanceEnabledInterface;
use Magento\LoginAsCustomerAssistance\Model\ResourceModel\GetLoginAsCustomerAssistanceAllowed;
use Magento\TestFramework\Authentication\OauthHelper;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Api tests for @see \Magento\LoginAsCustomerAssistance\Plugin\CustomerPlugin::afterSave.
 */
class CustomerPluginTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'customerCustomerRepositoryV1';
    const RESOURCE_PATH = '/V1/customers';

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var GetLoginAsCustomerAssistanceAllowed
     */
    private $isAssistanceEnabled;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->dataObjectProcessor = $objectManager->get(DataObjectProcessor::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->customerRegistry = $objectManager->get(CustomerRegistry::class);
        $this->isAssistanceEnabled = $objectManager->get(GetLoginAsCustomerAssistanceAllowed::class);
    }

    /**
     * Check that 'assistance_allowed' set as expected.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @dataProvider assistanceStatesDataProvider
     *
     * @param int $state
     * @param bool $expected
     * @return void
     */
    public function testUpdateCustomer(int $state, bool $expected): void
    {
        $customerId = (int)$this->customerRepository->get('customer@example.com')->getId();

        $updatedLastname = 'Updated lastname';
        $customer = $this->getCustomerData($customerId);
        $customerData = $this->dataObjectProcessor->buildOutputDataArray($customer, Customer::class);
        $customerData[Customer::LASTNAME] = $updatedLastname;
        $customerData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['assistance_allowed'] = $state;

        $requestData['customer'] = TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP
            ? $customerData
            : [
                Customer::LASTNAME => $updatedLastname,
                Customer::EXTENSION_ATTRIBUTES_KEY => ['assistance_allowed' => $state]
            ];

        $serviceInfo = $this->getServiceInfo($customerId, 'Save');
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($response);

        $existingCustomerDataObject = $this->getCustomerData($customerId);
        $this->assertEquals($updatedLastname, $existingCustomerDataObject->getLastname());
        $this->assertEquals($expected, $this->isAssistanceEnabled->execute($customerId));
    }

    /**
     * Check that 'assistance_allowed' set as expected with limited resources.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @dataProvider assistanceStatesDataProvider
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @param int $state
     * @return void
     */
    public function testUpdateCustomerWithLimitedResources(int $state): void
    {
        $resources = [
            'Magento_Customer::customer',
            'Magento_Customer::manage',
        ];
        $customerId = (int)$this->customerRepository->get('customer@example.com')->getId();

        $updatedLastname = 'Updated lastname';
        $customer = $this->getCustomerData($customerId);
        $customerData = $this->dataObjectProcessor->buildOutputDataArray($customer, Customer::class);
        $customerData[Customer::LASTNAME] = $updatedLastname;
        $customerData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['assistance_allowed'] = $state;

        $requestData['customer'] = TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP
            ? $customerData
            : [
                Customer::LASTNAME => $updatedLastname,
                Customer::EXTENSION_ATTRIBUTES_KEY => ['assistance_allowed' => $state]
            ];

        $serviceInfo = $this->getServiceInfo($customerId, 'Save');
        OauthHelper::getApiAccessCredentials($resources);
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($response);

        $existingCustomerDataObject = $this->getCustomerData($customerId);
        $this->assertEquals($updatedLastname, $existingCustomerDataObject->getLastname());
        $this->assertEquals(false, $this->isAssistanceEnabled->execute($customerId));
    }

    /**
     * @param int $customerId
     * @param string $operation
     * @return array
     */
    private function getServiceInfo(int $customerId, string $operation): array
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . $operation,
            ],
        ];
    }

    /**
     * Retrieve customer data by Id.
     *
     * @param int $customerId
     * @return Customer
     */
    private function getCustomerData(int $customerId): Customer
    {
        $customerData = $this->customerRepository->getById($customerId);
        $this->customerRegistry->remove($customerId);

        return $customerData;
    }

    /**
     * @return array
     */
    public function assistanceStatesDataProvider(): array
    {
        return [
            'Assistance Allowed' => [IsAssistanceEnabledInterface::ALLOWED, true],
            'Assistance Denied' => [IsAssistanceEnabledInterface::DENIED, false],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        OauthHelper::clearApiAccessCredentials();
        parent::tearDown();
    }
}
