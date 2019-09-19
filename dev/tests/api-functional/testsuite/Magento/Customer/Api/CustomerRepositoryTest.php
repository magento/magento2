<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

use Exception;
use Magento\Customer\Api\Data\AddressInterface as Address;
use Magento\Customer\Api\Data\CustomerInterface as Customer;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;
use Magento\TestFramework\TestCase\WebapiAbstract;
use SoapFault;

/**
 * Test class for Magento\Customer\Api\CustomerRepositoryInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'customerCustomerRepositoryV1';
    const RESOURCE_PATH = '/V1/customers';
    const RESOURCE_PATH_CUSTOMER_TOKEN = "/V1/integration/customer/token";

    /**
     * Sample values for testing
     */
    const ATTRIBUTE_CODE = 'attribute_code';
    const ATTRIBUTE_VALUE = 'attribute_value';

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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var FilterGroupBuilder
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
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->customerRegistry = Bootstrap::getObjectManager()->get(
            CustomerRegistry::class
        );

        $this->customerRepository = Bootstrap::getObjectManager()->get(
            CustomerRepositoryInterface::class,
            ['customerRegistry' => $this->customerRegistry]
        );
        $this->dataObjectHelper = Bootstrap::getObjectManager()->create(
            DataObjectHelper::class
        );
        $this->customerDataFactory = Bootstrap::getObjectManager()->create(
            CustomerInterfaceFactory::class
        );
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(
            SearchCriteriaBuilder::class
        );
        $this->sortOrderBuilder = Bootstrap::getObjectManager()->create(
            SortOrderBuilder::class
        );
        $this->filterGroupBuilder = Bootstrap::getObjectManager()->create(
            FilterGroupBuilder::class
        );
        $this->customerHelper = new CustomerHelper();

        $this->dataObjectProcessor = Bootstrap::getObjectManager()->create(
            DataObjectProcessor::class
        );
    }

    public function tearDown()
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
     * Validate update by invalid customer.
     *
     * @expectedException Exception
     */
    public function testInvalidCustomerUpdate()
    {
        //Create first customer and retrieve customer token.
        $firstCustomerData = $this->_createCustomer();

        // get customer ID token
        /** @var CustomerTokenServiceInterface $customerTokenService */
        //$customerTokenService = $this->objectManager->create(CustomerTokenServiceInterface::class);
        $customerTokenService = Bootstrap::getObjectManager()->create(
            CustomerTokenServiceInterface::class
        );
        $token = $customerTokenService->createCustomerAccessToken($firstCustomerData[Customer::EMAIL], 'test@123');

        //Create second customer and update lastname.
        $customerData = $this->_createCustomer();
        $existingCustomerDataObject = $this->_getCustomerData($customerData[Customer::ID]);
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
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $response = $this->_webApiCall($serviceInfo, ['customerId' => $customerData['id']]);
        } else {
            $response = $this->_webApiCall($serviceInfo);
        }

        $this->assertTrue($response);

        //Verify if the customer is deleted
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage(sprintf("No such entity with customerId = %s", $customerData[Customer::ID]));
        $this->_getCustomerData($customerData[Customer::ID]);
    }

    public function testDeleteCustomerInvalidCustomerId()
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
        } catch (SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(['fieldName' => 'customerId', 'fieldValue' => $invalidId], $errorObj['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    public function testUpdateCustomer()
    {
        $customerData = $this->_createCustomer();
        $existingCustomerDataObject = $this->_getCustomerData($customerData[Customer::ID]);
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
        $requestData = ['customer' => $newCustomerDataObject];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($response !== null);

        //Verify if the customer is updated
        $existingCustomerDataObject = $this->_getCustomerData($customerData[Customer::ID]);
        $this->assertEquals($lastName . "Updated", $existingCustomerDataObject->getLastname());
    }

    /**
     * Verify expected behavior when the website id is not set
     */
    public function testUpdateCustomerNoWebsiteId()
    {
        $customerData = $this->customerHelper->createSampleCustomer();
        $existingCustomerDataObject = $this->_getCustomerData($customerData[Customer::ID]);
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
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail("Expected exception.");
        } catch (SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (Exception $e) {
            $errorObj =  $this->customerHelper->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message'], 'Invalid message: "' . $e->getMessage() . '"');
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
        }
    }

    public function testUpdateCustomerException()
    {
        $customerData = $this->_createCustomer();
        $existingCustomerDataObject = $this->_getCustomerData($customerData[Customer::ID]);
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
        } catch (SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(['fieldName' => 'customerId', 'fieldValue' => -1], $errorObj['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    /**
     * Test creating a customer with absent required address fields
     */
    public function testCreateCustomerWithoutAddressRequiresException()
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
        } catch (Exception $e) {
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
     */
    public function testSearchCustomers()
    {
        $builder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
        $customerData = $this->_createCustomer();
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
        } catch (Exception $e) {
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

        /**@var SortOrderBuilder $sortOrderBuilder */
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

        /** @var FilterBuilder $builder */
        $builder = Bootstrap::getObjectManager()->create(FilterBuilder::class);

        /** @var SortOrderBuilder $sortBuilder */
        $sortBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);
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
        $sort = $sortBuilder->setField(Customer::EMAIL)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();
        $this->searchCriteriaBuilder->setSortOrders([$sort]);
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

        /** @var FilterBuilder $builder */
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
     * Retrieve customer data by Id
     *
     * @param int $customerId
     * @return Customer
     */
    protected function _getCustomerData($customerId)
    {
        $customerData =  $this->customerRepository->getById($customerId);
        $this->customerRegistry->remove($customerId);
        return $customerData;
    }

    /**
     * @return array|bool|float|int|string
     */
    protected function _createCustomer()
    {
        $customerData = $this->customerHelper->createSampleCustomer();
        $this->currentCustomerId[] = $customerData['id'];
        return $customerData;
    }
}
