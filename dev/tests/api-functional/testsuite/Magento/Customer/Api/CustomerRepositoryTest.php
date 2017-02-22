<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface as Customer;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\InputException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;

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
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
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
     * @var \Magento\Customer\Model\CustomerRegistry
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
    public function setUp()
    {
        $this->customerRegistry = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Model\CustomerRegistry'
        );

        $this->customerRepository = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\CustomerRepositoryInterface',
            ['customerRegistry' => $this->customerRegistry]
        );
        $this->dataObjectHelper = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\DataObjectHelper'
        );
        $this->customerDataFactory = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\Data\CustomerInterfaceFactory'
        );
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );
        $this->sortOrderBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\SortOrderBuilder'
        );
        $this->filterGroupBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\Search\FilterGroupBuilder'
        );
        $this->customerHelper = new CustomerHelper();

        $this->dataObjectProcessor = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Reflection\DataObjectProcessor'
        );
    }

    public function tearDown()
    {
        if (!empty($this->currentCustomerId)) {
            foreach ($this->currentCustomerId as $customerId) {
                $serviceInfo = [
                    'rest' => [
                        'resourcePath' => self::RESOURCE_PATH . '/' . $customerId,
                        'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
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
        unset($this->customerRepository);
    }

    public function testDeleteCustomer()
    {
        $customerData = $this->_createCustomer();
        $this->currentCustomerId = [];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerData[Customer::ID],
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
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
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            sprintf("No such entity with customerId = %s", $customerData[Customer::ID])
        );
        $this->_getCustomerData($customerData[Customer::ID]);
    }

    public function testDeleteCustomerInvalidCustomerId()
    {
        $invalidId = -1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $invalidId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
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
            $this->assertContains(
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
            '\Magento\Customer\Api\Data\CustomerInterface'
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/{$customerData[Customer::ID]}",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $newCustomerDataObject = $this->dataObjectProcessor->buildOutputDataArray(
            $newCustomerDataObject,
            'Magento\Customer\Api\Data\CustomerInterface'
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
            '\Magento\Customer\Api\Data\CustomerInterface'
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/{$customerData[Customer::ID]}",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $newCustomerDataObject = $this->dataObjectProcessor->buildOutputDataArray(
            $newCustomerDataObject,
            'Magento\Customer\Api\Data\CustomerInterface'
        );
        unset($newCustomerDataObject['website_id']);
        $requestData = ['customer' => $newCustomerDataObject];

        $expectedMessage = '"Associate to Website" is a required value.';
        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail("Expected exception.");
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
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
            '\Magento\Customer\Api\Data\CustomerInterface'
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/-1",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $newCustomerDataObject = $this->dataObjectProcessor->buildOutputDataArray(
            $newCustomerDataObject,
            'Magento\Customer\Api\Data\CustomerInterface'
        );
        $requestData = ['customer' => $newCustomerDataObject];

        $expectedMessage = 'No such entity with %fieldName = %fieldValue';

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail("Expected exception.");
        } catch (\SoapFault $e) {
            $this->assertContains(
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
     * Test with a single filter
     */
    public function testSearchCustomers()
    {
        $builder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\FilterBuilder');
        $customerData = $this->_createCustomer();
        $filter = $builder
            ->setField(Customer::EMAIL)
            ->setValue($customerData[Customer::EMAIL])
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter]);
        $searchData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->searchCriteriaBuilder->create(),
            'Magento\Framework\Api\SearchCriteriaInterface'
        );
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
        $builder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\FilterBuilder');
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo);
        } catch (\Exception $e) {
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
            $exceptionData = $this->processRestExceptionResult($e);
            $expectedExceptionData = [
                'message' => InputException::REQUIRED_FIELD,
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
        $builder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\FilterBuilder');
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
            'Magento\Framework\Api\SortOrderBuilder'
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
        $builder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\FilterBuilder');
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
        $builder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\FilterBuilder');
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
        $builder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\FilterBuilder');
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
        $builder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\FilterBuilder');
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
     * @return \Magento\Customer\Api\Data\CustomerInterface
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
