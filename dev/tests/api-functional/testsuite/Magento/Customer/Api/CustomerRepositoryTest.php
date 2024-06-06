<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\AddressInterface as Address;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\Data\CustomerInterface as Customer;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test for \Magento\Customer\Api\CustomerRepositoryInterface.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerRepositoryTest extends WebapiAbstract
{
    public const SERVICE_VERSION = 'V1';
    public const SERVICE_NAME = 'customerCustomerRepositoryV1';
    public const RESOURCE_PATH = '/V1/customers';

    private const STUB_INVALID_CUSTOMER_GROUP_ID = 777;

    private const STUB_RETAILER_GROUP_ID = 3;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerDataFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var array
     */
    private $currentCustomerId;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * Execute per test initialization.
     */
    protected function setUp(): void
    {
        $this->customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);

        $this->customerRepository = Bootstrap::getObjectManager()->get(
            CustomerRepositoryInterface::class,
            ['customerRegistry' => $this->customerRegistry]
        );
        $this->dataObjectHelper = Bootstrap::getObjectManager()->create(DataObjectHelper::class);
        $this->customerDataFactory = Bootstrap::getObjectManager()->create(CustomerInterfaceFactory::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $this->sortOrderBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);
        $this->filterGroupBuilder = Bootstrap::getObjectManager()->create(FilterGroupBuilder::class);
        $this->customerHelper = new CustomerHelper();

        $this->dataObjectProcessor = Bootstrap::getObjectManager()->create(DataObjectProcessor::class);
    }

    protected function tearDown(): void
    {
        if (!empty($this->currentCustomerId)) {
            foreach ($this->currentCustomerId as $customerId) {
                $serviceInfo = [
                    'rest' => [
                        'resourcePath' => self::RESOURCE_PATH . '/' . $customerId,
                        'httpMethod' => Request::HTTP_METHOD_DELETE,
                    ],
                    'soap' => [
                        'service' => self::SERVICE_NAME,
                        'serviceVersion' => self::SERVICE_VERSION,
                        'operation' => self::SERVICE_NAME . 'DeleteById',
                    ],
                ];

                $response = $this->_webApiCall($serviceInfo, ['customerId' => $customerId]);

                $this->assertTrue($response);
            }
        }
        $this->customerRepository = null;
    }

    /**
     * Validate update operation by invalid customer
     *
     */
    public function testInvalidCustomerUpdate()
    {
        $this->expectException(\Exception::class);

        //Create first customer and retrieve customer token.
        $firstCustomerData = $this->_createCustomer();

        //Get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = Bootstrap::getObjectManager()->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $token = $customerTokenService->createCustomerAccessToken(
            $firstCustomerData[Customer::EMAIL],
            'test@123'
        );

        //Create second customer and update lastname.
        $customerData = $this->_createCustomer();
        $existingCustomerDataObject = $this->getCustomerData($customerData[Customer::ID]);
        $lastName = $existingCustomerDataObject->getLastname();
        $customerData[Customer::LASTNAME] = $lastName . 'Updated';
        $newCustomerDataObject = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $newCustomerDataObject,
            $customerData,
            Customer::class
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/{$customerData[Customer::ID]}",
                'httpMethod' => Request::HTTP_METHOD_PUT,
                'token' => $token,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
                'token' => $token
            ]
        ];

        $newCustomerDataObject = $this->dataObjectProcessor->buildOutputDataArray(
            $newCustomerDataObject,
            Customer::class
        );
        $requestData = ['customer' => $newCustomerDataObject];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Create Integration and return token.
     *
     * @param string $name
     * @param array $resource
     * @return string
     */
    private function createIntegrationToken(string $name, array $resource): string
    {
        /** @var IntegrationServiceInterface $integrationService */
        $integrationService = Bootstrap::getObjectManager()->get(IntegrationServiceInterface::class);
        $oauthService = Bootstrap::getObjectManager()->get(OauthServiceInterface::class);
        /** @var Integration $integration */
        $integration = $integrationService->create(
            [
                'name' => $name,
                'resource' => $resource,
            ]
        );
        /** @var OauthServiceInterface $oauthService */
        $oauthService->createAccessToken($integration->getConsumerId());

        return $integrationService->get($integration->getId())->getToken();
    }

    public function testDeleteCustomer()
    {
        $customerData = $this->_createCustomer();
        $this->currentCustomerId = [];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerData[Customer::ID],
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP)
            ? $this->_webApiCall($serviceInfo, ['customerId' => $customerData['id']])
            : $this->_webApiCall($serviceInfo);

        $this->assertTrue($response);

        //Verify if the customer is deleted
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage(sprintf("No such entity with customerId = %s", $customerData[Customer::ID]));
        $this->getCustomerData($customerData[Customer::ID]);
    }

    /**
     * Check that non-authorized consumer can`t delete customer.
     *
     * @return void
     */
    public function testDeleteCustomerNonAuthorized(): void
    {
        $resource = [
            'Magento_Customer::customer',
            'Magento_Customer::manage',
        ];
        $token = $this->createIntegrationToken('TestAPI' . bin2hex(random_bytes(5)), $resource);

        $customerData = $this->_createCustomer();
        $this->currentCustomerId = [];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerData[Customer::ID],
                'httpMethod' => Request::HTTP_METHOD_DELETE,
                'token' => $token,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
                'token' => $token,
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo, ['customerId' => $customerData['id']]);
            $this->fail("Expected exception is not thrown.");
        } catch (\SoapFault $e) {
        } catch (\Exception $e) {
            $expectedMessage = 'The consumer isn\'t authorized to access %resources.';
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(['resources' => 'Magento_Customer::delete'], $errorObj['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_UNAUTHORIZED, $e->getCode());
        }
        /** @var Customer $data */
        $data = $this->getCustomerData($customerData[Customer::ID]);
        $this->assertNotNull($data->getId());
    }

    /**
     * Test delete customer with invalid id
     *
     * @return void
     */
    public function testDeleteCustomerInvalidCustomerId(): void
    {
        $invalidId = -1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $invalidId,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        $expectedMessage = 'No such entity with %fieldName = %fieldValue';

        try {
            $this->_webApiCall($serviceInfo, ['customerId' => $invalidId]);

            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(['fieldName' => 'customerId', 'fieldValue' => $invalidId], $errorObj['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    /**
     * Test customer update
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testUpdateCustomer(): void
    {
        $customerId = 1;
        $updatedLastName = 'Updated lastname';
        $customer = $this->getCustomerData($customerId);
        $customerData = $this->dataObjectProcessor->buildOutputDataArray($customer, Customer::class);
        $customerData[Customer::LASTNAME] = $updatedLastName;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData['customer'] = (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP)
            ? $customerData
            : [
                Customer::FIRSTNAME => $customer->getFirstname(),
                Customer::LASTNAME => $updatedLastName,
                Customer::EMAIL => $customer->getEmail(),
                Customer::WEBSITE_ID => $customer->getWebsiteId(),
                Customer::ID => $customerId,
            ];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($response);

        //Verify if the customer is updated
        $existingCustomerDataObject = $this->getCustomerData($customerId);
        $this->assertEquals($updatedLastName, $existingCustomerDataObject->getLastname());
        $this->assertEquals($customerData[Customer::FIRSTNAME], $existingCustomerDataObject->getFirstname());
    }

    /**
     * Verify expected behavior when the website id is not set
     */
    public function testUpdateCustomerNoWebsiteId()
    {
        $customerData = $this->customerHelper->createSampleCustomer();
        $existingCustomerDataObject = $this->getCustomerData($customerData[Customer::ID]);
        $lastName = $existingCustomerDataObject->getLastname();
        $customerData[Customer::LASTNAME] = $lastName . 'Updated';
        $newCustomerDataObject = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $newCustomerDataObject,
            $customerData,
            Customer::class
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/{$customerData[Customer::ID]}",
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $newCustomerDataObject = $this->dataObjectProcessor->buildOutputDataArray(
            $newCustomerDataObject,
            Customer::class
        );
        unset($newCustomerDataObject['website_id']);
        $requestData = ['customer' => $newCustomerDataObject];
        $expectedMessage = '"Associate to Website" is a required value.';
        try {
            $response = $this->_webApiCall($serviceInfo, $requestData);
            $this->assertEquals($customerData['website_id'], $response['website_id']);
        } catch (\SoapFault $e) {
            $this->assertStringContainsString($expectedMessage, $e->getMessage());
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
            $this->assertEquals($expectedMessage, $errorObj['message']);
        }
    }

    /**
     * Test customer exception update
     *
     * @return void
     */
    public function testUpdateCustomerException(): void
    {
        $customerData = $this->_createCustomer();
        $existingCustomerDataObject = $this->getCustomerData($customerData[Customer::ID]);
        $lastName = $existingCustomerDataObject->getLastname();

        //Set non-existent id = -1
        $customerData[Customer::LASTNAME] = $lastName . 'Updated';
        $customerData[Customer::ID] = -1;
        $newCustomerDataObject = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $newCustomerDataObject,
            $customerData,
            Customer::class
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/-1",
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $newCustomerDataObject = $this->dataObjectProcessor->buildOutputDataArray(
            $newCustomerDataObject,
            Customer::class
        );
        $requestData = ['customer' => $newCustomerDataObject];

        $expectedMessage = 'No such entity with %fieldName = %fieldValue';

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail("Expected exception.");
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(['fieldName' => 'customerId', 'fieldValue' => -1], $errorObj['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    /**
     * Test customer update with invalid customer group id
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testUpdateCustomerWithInvalidGroupId(): void
    {
        $customerId = 1;
        $customerData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->getCustomerData($customerId),
            Customer::class
        );
        $customerData[Customer::GROUP_ID] = self::STUB_INVALID_CUSTOMER_GROUP_ID;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData['customer'] = $customerData;
        $expectedMessage = 'The specified customer group id does not exist.';

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Expected exception was not raised');
        } catch (\SoapFault $e) {
            $this->assertStringContainsString($expectedMessage, $e->getMessage());
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
            $this->assertEquals($expectedMessage, $errorObj['message']);
        }
    }

    /**
     * Test customer update quote with valid customer group id change
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testUpdateCustomerQuoteOnGroupIdChange(): void
    {
        $customerId = 1;
        $customerData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->getCustomerData($customerId),
            Customer::class
        );
        $customerData[Customer::GROUP_ID] = self::STUB_RETAILER_GROUP_ID;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData['customer'] = $customerData;

        $updateResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($updateResults['group_id'], self::STUB_RETAILER_GROUP_ID);
    }

    /**
     * Test customer create with invalid customer group id
     *
     * @return void
     */
    public function testCreateCustomerWithInvalidGroupId(): void
    {
        $customerData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerHelper->createSampleCustomerDataObject(),
            Customer::class
        );
        $customerData[Customer::GROUP_ID] = self::STUB_INVALID_CUSTOMER_GROUP_ID;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData = ['customer' => $customerData];
        $expectedMessage = 'The specified customer group id does not exist.';

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Expected exception was not raised');
        } catch (\SoapFault $e) {
            $this->assertStringContainsString($expectedMessage, $e->getMessage());
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
            $this->assertEquals($expectedMessage, $errorObj['message']);
        }
    }

    /**
     * Test creating a customer with absent required address fields
     *
     * @return void
     */
    public function testCreateCustomerWithoutAddressRequiresException(): void
    {
        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerHelper->createSampleCustomerDataObject(),
            Customer::class
        );

        foreach ($customerDataArray[Customer::KEY_ADDRESSES] as & $address) {
            $address[Address::FIRSTNAME] = null;
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['customer' => $customerDataArray];
        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Expected exception did not occur.');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
                $expectedException = new InputException();
                $expectedException->addError(
                    __(
                        '"%fieldName" is required. Enter and try again.',
                        ['fieldName' => Address::FIRSTNAME]
                    )
                );
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault(
                    $e,
                    $expectedException->getRawMessage(),
                    'env:Sender',
                    $expectedException->getParameters() // expected error parameters
                );
            } else {
                $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
                $exceptionData = $this->processRestExceptionResult($e);
                $expectedExceptionData = [
                    'message' => '"%fieldName" is required. Enter and try again.',
                    'parameters' => ['fieldName' => Address::FIRSTNAME],
                ];
                $this->assertEquals($expectedExceptionData, $exceptionData);
            }
        }

        try {
            $this->customerRegistry->retrieveByEmail(
                $customerDataArray[Customer::EMAIL],
                $customerDataArray[Customer::WEBSITE_ID]
            );
            $this->fail('An expected NoSuchEntityException was not thrown.');
        } catch (NoSuchEntityException $e) {
            $exception = NoSuchEntityException::doubleField(
                'email',
                $customerDataArray[Customer::EMAIL],
                'websiteId',
                $customerDataArray[Customer::WEBSITE_ID]
            );
            $this->assertEquals(
                $exception->getMessage(),
                $e->getMessage(),
                'Exception message does not match expected message.'
            );
        }
    }

    /**
     * Test with a single filter
     *
     * @param bool $subscribeStatus
     * @return void
     *
     * @dataProvider subscriptionDataProvider
     */
    public function testSearchCustomers(bool $subscribeStatus): void
    {
        $builder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
        $subscribeData = $this->buildSubscriptionData($subscribeStatus);
        $customerData = $this->_createCustomer($subscribeData);
        $filter = $builder
            ->setField(Customer::EMAIL)
            ->setValue($customerData[Customer::EMAIL])
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter]);
        $searchData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->searchCriteriaBuilder->create(),
            SearchCriteriaInterface::class
        );
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getList',
            ],
        ];
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(1, $searchResults['total_count']);
        $this->assertEquals($customerData[Customer::ID], $searchResults['items'][0][Customer::ID]);
        $this->assertEquals($subscribeStatus, $searchResults['items'][0]['extension_attributes']['is_subscribed']);
    }

    /**
     * Build subscription extension attributes data
     *
     * @param bool $status
     * @return array
     */
    private function buildSubscriptionData(bool $status): array
    {
        return [
            'extension_attributes' => [
                'is_subscribed' => $status,
            ],
        ];
    }

    /**
     * Subscription customer data provider
     *
     * @return array
     */
    public function subscriptionDataProvider(): array
    {
        return [
            'subscribed user' => [true],
            'not subscribed user' => [false],
        ];
    }

    /**
     * Test with a single filter using GET
     */
    public function testSearchCustomersUsingGET()
    {
        $this->_markTestAsRestOnly('SOAP test is covered in testSearchCustomers');
        $builder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
        $customerData = $this->_createCustomer();
        $filter = $builder
            ->setField(Customer::EMAIL)
            ->setValue($customerData[Customer::EMAIL])
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter]);

        $searchData = $this->searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $searchQueryString = http_build_query($requestData);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search?' . $searchQueryString,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        $searchResults = $this->_webApiCall($serviceInfo);
        $this->assertEquals(1, $searchResults['total_count']);
        $this->assertEquals($customerData[Customer::ID], $searchResults['items'][0][Customer::ID]);
    }

    /**
     * Test with empty GET based filter
     */
    public function testSearchCustomersUsingGETEmptyFilter()
    {
        $this->_markTestAsRestOnly('Soap clients explicitly check for required fields based on WSDL.');
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo);
        } catch (\Exception $e) {
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
            $exceptionData = $this->processRestExceptionResult($e);
            $expectedExceptionData = [
                'message' => '"%fieldName" is required. Enter and try again.',
                'parameters' => [
                    'fieldName' => 'searchCriteria'
                ],
            ];
            $this->assertEquals($expectedExceptionData, $exceptionData);
        }
    }

    /**
     * Test using multiple filters
     */
    public function testSearchCustomersMultipleFiltersWithSort()
    {
        $builder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
        $customerData1 = $this->_createCustomer();
        $customerData2 = $this->_createCustomer();
        $filter1 = $builder->setField(Customer::EMAIL)
            ->setValue($customerData1[Customer::EMAIL])
            ->create();
        $filter2 = $builder->setField(Customer::EMAIL)
            ->setValue($customerData2[Customer::EMAIL])
            ->create();
        $filter3 = $builder->setField(Customer::LASTNAME)
            ->setValue($customerData1[Customer::LASTNAME])
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $this->searchCriteriaBuilder->addFilters([$filter3]);

        /**@var \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = Bootstrap::getObjectManager()->create(
            SortOrderBuilder::class
        );
        /** @var SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField(Customer::EMAIL)->setDirection(SortOrder::SORT_ASC)->create();
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchData = $searchCriteria->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getList',
            ],
        ];
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(2, $searchResults['total_count']);
        $this->assertEquals($customerData1[Customer::ID], $searchResults['items'][0][Customer::ID]);
        $this->assertEquals($customerData2[Customer::ID], $searchResults['items'][1][Customer::ID]);
    }

    /**
     * Test using multiple filters using GET
     */
    public function testSearchCustomersMultipleFiltersWithSortUsingGET()
    {
        $this->_markTestAsRestOnly('SOAP test is covered in testSearchCustomers');
        $builder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
        $customerData1 = $this->_createCustomer();
        $customerData2 = $this->_createCustomer();
        $filter1 = $builder->setField(Customer::EMAIL)
            ->setValue($customerData1[Customer::EMAIL])
            ->create();
        $filter2 = $builder->setField(Customer::EMAIL)
            ->setValue($customerData2[Customer::EMAIL])
            ->create();
        $filter3 = $builder->setField(Customer::LASTNAME)
            ->setValue($customerData1[Customer::LASTNAME])
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $this->searchCriteriaBuilder->addFilters([$filter3]);
        $this->searchCriteriaBuilder->setSortOrders([Customer::EMAIL => SortOrder::SORT_ASC]);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchData = $searchCriteria->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $searchQueryString = http_build_query($requestData);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search?' . $searchQueryString,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        $searchResults = $this->_webApiCall($serviceInfo);
        $this->assertEquals(2, $searchResults['total_count']);
        $this->assertEquals($customerData1[Customer::ID], $searchResults['items'][0][Customer::ID]);
        $this->assertEquals($customerData2[Customer::ID], $searchResults['items'][1][Customer::ID]);
    }

    /**
     * Test and verify multiple filters using And-ed non-existent filter value
     */
    public function testSearchCustomersNonExistentMultipleFilters()
    {
        $builder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
        $customerData1 = $this->_createCustomer();
        $customerData2 = $this->_createCustomer();
        $filter1 = $filter1 = $builder->setField(Customer::EMAIL)
            ->setValue($customerData1[Customer::EMAIL])
            ->create();
        $filter2 = $builder->setField(Customer::EMAIL)
            ->setValue($customerData2[Customer::EMAIL])
            ->create();
        $filter3 = $builder->setField(Customer::LASTNAME)
            ->setValue('INVALID')
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $this->searchCriteriaBuilder->addFilters([$filter3]);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchData = $searchCriteria->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getList',
            ],
        ];
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(0, $searchResults['total_count'], 'No results expected for non-existent email.');
    }

    /**
     * Test and verify multiple filters using And-ed non-existent filter value using GET
     */
    public function testSearchCustomersNonExistentMultipleFiltersGET()
    {
        $this->_markTestAsRestOnly('SOAP test is covered in testSearchCustomers');
        $builder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
        $customerData1 = $this->_createCustomer();
        $customerData2 = $this->_createCustomer();
        $filter1 = $filter1 = $builder->setField(Customer::EMAIL)
            ->setValue($customerData1[Customer::EMAIL])
            ->create();
        $filter2 = $builder->setField(Customer::EMAIL)
            ->setValue($customerData2[Customer::EMAIL])
            ->create();
        $filter3 = $builder->setField(Customer::LASTNAME)
            ->setValue('INVALID')
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $this->searchCriteriaBuilder->addFilters([$filter3]);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchData = $searchCriteria->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $searchQueryString = http_build_query($requestData);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search?' . $searchQueryString,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(0, $searchResults['total_count'], 'No results expected for non-existent email.');
    }

    /**
     * Test using multiple filters
     */
    public function testSearchCustomersMultipleFilterGroups()
    {
        $customerData1 = $this->_createCustomer();

        /** @var \Magento\Framework\Api\FilterBuilder $builder */
        $builder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
        $filter1 = $builder->setField(Customer::EMAIL)
            ->setValue($customerData1[Customer::EMAIL])
            ->create();
        $filter2 = $builder->setField(Customer::MIDDLENAME)
            ->setValue($customerData1[Customer::MIDDLENAME])
            ->create();
        $filter3 = $builder->setField(Customer::MIDDLENAME)
            ->setValue('invalid')
            ->create();
        $filter4 = $builder->setField(Customer::LASTNAME)
            ->setValue($customerData1[Customer::LASTNAME])
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter1]);
        $this->searchCriteriaBuilder->addFilters([$filter2, $filter3]);
        $this->searchCriteriaBuilder->addFilters([$filter4]);
        $searchCriteria = $this->searchCriteriaBuilder->setCurrentPage(1)->setPageSize(10)->create();
        $searchData = $searchCriteria->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getList',
            ],
        ];
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(1, $searchResults['total_count']);
        $this->assertEquals($customerData1[Customer::ID], $searchResults['items'][0][Customer::ID]);

        // Add an invalid And-ed data with multiple groups to yield no result
        $filter4 = $builder->setField(Customer::LASTNAME)
            ->setValue('invalid')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter1]);
        $this->searchCriteriaBuilder->addFilters([$filter2, $filter3]);
        $this->searchCriteriaBuilder->addFilters([$filter4]);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchData = $searchCriteria->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo['rest']['resourcePath'] = self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData);
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(0, $searchResults['total_count']);
    }

    /**
     * Test revoking all access Tokens for customer
     */
    public function testRevokeAllAccessTokensForCustomer()
    {
        $customerData = $this->_createCustomer();

        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = Bootstrap::getObjectManager()->create(CustomerTokenServiceInterface::class);
        $token = $customerTokenService->createCustomerAccessToken(
            $customerData[Customer::EMAIL],
            CustomerHelper::PASSWORD
        );
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/me',
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $token,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetSelf',
                'token' => $token,
            ],
        ];

        $customerLoadedData = $this->_webApiCall($serviceInfo, ['customerId' => $customerData[Customer::ID]]);
        self::assertGreaterThanOrEqual($customerData[Customer::UPDATED_AT], $customerLoadedData[Customer::UPDATED_AT]);
        unset($customerData[Customer::UPDATED_AT]);
        unset($customerLoadedData[Customer::UPDATED_AT], $customerLoadedData[Customer::CONFIRMATION]);
        self::assertEquals($customerData, $customerLoadedData);

        $revokeToken = $customerTokenService->revokeCustomerAccessToken($customerData[Customer::ID]);
        self::assertTrue($revokeToken);

        try {
            $customerTokenService->revokeCustomerAccessToken($customerData[Customer::ID]);
        } catch (\Throwable $exception) {
            $this->assertInstanceOf(LocalizedException::class, $exception);
            $this->assertEquals('This customer has no tokens.', $exception->getMessage());
        }

        $expectedMessage = 'The consumer isn\'t authorized to access %resources.';

        try {
            $this->_webApiCall($serviceInfo, ['customerId' => $customerData[Customer::ID]]);
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                'SoapFault does not contain expected message.'
            );
        } catch (\Throwable $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(['resources' => 'self'], $errorObj['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_UNAUTHORIZED, $e->getCode());
        }
    }

    /**
     * Retrieve customer data by Id
     *
     * @param int $customerId
     * @return Customer
     */
    private function getCustomerData($customerId): Customer
    {
        $customerData = $this->customerRepository->getById($customerId);
        $this->customerRegistry->remove($customerId);
        return $customerData;
    }

    /**
     * @param array|null $additionalData
     * @return array|bool|float|int|string
     */
    protected function _createCustomer(?array $additionalData = [])
    {
        $customerData = $this->customerHelper->createSampleCustomer($additionalData);
        $this->currentCustomerId[] = $customerData['id'];
        return $customerData;
    }

    /**
     * Test customer create with invalid name's.
     *
     * @param string $fieldName
     * @param string $fieldValue
     * @param string $expectedMessage
     * @return void
     *
     * @dataProvider customerDataProvider
     */
    public function testCreateCustomerWithInvalidCustomerFirstName(
        string $fieldName,
        string $fieldValue,
        string $expectedMessage
    ): void {
        $customerData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerHelper->createSampleCustomerDataObject(),
            Customer::class
        );
        $customerData[$fieldName] = $fieldValue;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData = ['customer' => $customerData];

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Expected exception was not raised');
        } catch (\SoapFault $e) {
            $this->assertStringContainsString($expectedMessage, $e->getMessage());
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
            $this->assertEquals($expectedMessage, $errorObj['message']);
        }
    }

    /**
     * Invalid customer data provider
     *
     * @return array
     */
    public function customerDataProvider(): array
    {
        return [
            ['firstname', 'Jane ☺ ', 'First Name is not valid!'],
            ['lastname', '☏ - Doe', 'Last Name is not valid!'],
            ['middlename', '⚐ $(date)', 'Middle Name is not valid!'],
            [
                'firstname',
                str_repeat('खाना अच्छा है', 20),
                'First Name is not valid!',
            ],
            [
                'lastname',
                str_repeat('المغلوطة حول استنكار  النشوة وتمجيد الألمالمغلوطة حول', 5),
                'Last Name is not valid!',
            ],
        ];
    }

    /**
     * Test customer create with ultibyte chanracters in name's.
     *
     * @param string $fieldName
     * @param string $fieldValue
     * @return void
     *
     * @dataProvider customerWithMultiByteDataProvider
     */
    public function testCreateCustomerWithMultibyteCharacters(string $fieldName, string $fieldValue): void
    {
        $customerData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerHelper->createSampleCustomerDataObject(),
            Customer::class
        );
        $customerData[$fieldName] = $fieldValue;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData = ['customer' => $customerData];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertNotNull($response);
        $this->assertEquals($fieldValue, $response[$fieldName]);
    }

    /**
     * Customer with multibyte characters data provider.
     *
     * @return array
     */
    public function customerWithMultiByteDataProvider(): array
    {
        return [
            [
                'firstname',
                str_repeat('हैखान', 51),
            ],
            [
                'lastname',
                str_repeat('مغلوطة حول استنكار  النشوة وتمجيد الألمالمغلوطة حول', 5),
            ],
        ];
    }

    /**
     * Test customer create with valid name's.
     *
     * @param string $fieldName
     * @param string $fieldValue
     * @return void
     *
     * @dataProvider customerValidNameDataProvider
     */
    public function testCreateCustomerWithValidName(string $fieldName, string $fieldValue): void
    {
        $customerData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerHelper->createSampleCustomerDataObject(),
            Customer::class
        );
        $customerData[$fieldName] = $fieldValue;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData = ['customer' => $customerData];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertNotNull($response);
        $this->assertEquals($fieldValue, $response[$fieldName]);
    }

    /**
     * Customer valid name data provider.
     *
     * @return array
     */
    public function customerValidNameDataProvider(): array
    {
        return [
            [
                'firstname',
                'Anne-Marie',
            ],
            [
                'lastname',
                'D\'Artagnan',
            ],
            [
                'lastname',
                'Guðmundsdóttir',
            ],
            [
                'lastname',
                'María José Carreño Quiñones',
            ],
            [
                'lastname',
                'Q. Public',
            ],
            [
                'firstname',
                'Elizabeth II',
            ],
            [
                'firstname',
                'X Æ A-12 Musk',
            ],
        ];
    }
}
