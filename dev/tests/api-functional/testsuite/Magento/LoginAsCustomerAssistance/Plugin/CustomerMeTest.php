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
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\LoginAsCustomerAssistance\Api\IsAssistanceEnabledInterface;
use Magento\LoginAsCustomerAssistance\Model\ResourceModel\GetLoginAsCustomerAssistanceAllowed;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Api test for @see \Magento\LoginAsCustomerAssistance\Plugin\CustomerPlugin::afterSave.
 */
class CustomerMeTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'customerCustomerRepositoryV1';
    const RESOURCE_PATH = '/V1/customers/me';

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
     * @var TokenFactory
     */
    private $tokenFactory;

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
        $this->tokenFactory = $objectManager->get(TokenFactory::class);
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
    public function testUpdateSelf(int $state, bool $expected): void
    {
        $customerId = (int)$this->customerRepository->get('customer@example.com')->getId();
        $tokenModel = $this->tokenFactory->create();
        $customerToken = $tokenModel->createCustomerToken($customerId)->getToken();

        $updatedLastname = 'Updated lastname';
        $customer = $this->getCustomerData($customerId);
        $customerData = $this->dataObjectProcessor->buildOutputDataArray($customer, Customer::class);
        $customerData[Customer::LASTNAME] = $updatedLastname;
        $customerData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['assistance_allowed'] = $state;

        $requestData['customer'] = TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP
            ? $customerData
            : [
                Customer::EMAIL => $customerData['email'],
                Customer::FIRSTNAME => $customerData['firstname'],
                Customer::LASTNAME => $updatedLastname,
                Customer::EXTENSION_ATTRIBUTES_KEY => ['assistance_allowed' => $state],
            ];

        $serviceInfo = $this->getServiceInfo('SaveSelf', $customerToken);
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($response);

        $existingCustomerDataObject = $this->getCustomerData($customerId);
        $this->assertEquals($updatedLastname, $existingCustomerDataObject->getLastname());
        $this->assertEquals($expected, $this->isAssistanceEnabled->execute($customerId));
    }

    /**
     * @param string $operation
     * @param string $token
     * @return array
     */
    private function getServiceInfo(string $operation, string $token): array
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_PUT,
                'token' => $token,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . $operation,
                'token' => $token,
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
}
