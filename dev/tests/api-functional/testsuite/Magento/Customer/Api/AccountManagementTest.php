<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface as Customer;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Exception as HTTPExceptionCodes;
use Magento\Webapi\Model\Rest\Config as RestConfig;

/**
 * Test class for Magento\Customer\Api\AccountManagementInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountManagementTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'customerAccountManagementV1';
    const RESOURCE_PATH = '/V1/customers';

    /**
     * Sample values for testing
     */
    const ATTRIBUTE_CODE = 'attribute_code';
    const ATTRIBUTE_VALUE = 'attribute_value';

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Customer\Api\Data\AddressDataBuilder
     */
    private $addressBuilder;

    /**
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    private $customerBuilder;

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
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\AccountManagementInterface'
        );
        $this->addressBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\Data\AddressDataBuilder'
        );
        $this->customerBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\Data\CustomerDataBuilder'
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
                        'httpMethod' => RestConfig::HTTP_METHOD_DELETE,
                    ],
                    'soap' => [
                        'service' => CustomerRepositoryTest::SERVICE_NAME,
                        'serviceVersion' => self::SERVICE_VERSION,
                        'operation' => CustomerRepositoryTest::SERVICE_NAME . 'DeleteById',
                    ],
                ];

                $response = $this->_webApiCall($serviceInfo, ['customerId' => $customerId]);

                $this->assertTrue($response);
            }
        }
        unset($this->accountManagement);
    }

    public function testCreateCustomer()
    {
        $customerData = $this->_createCustomer();
        $this->assertNotNull($customerData['id']);
    }

    public function testCreateCustomerWithErrors()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => RestConfig::HTTP_METHOD_POST, ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'CreateAccount',
            ],
        ];

        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerHelper->createSampleCustomerDataObject(),
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $invalidEmail = 'invalid';
        $customerDataArray['email'] = $invalidEmail;
        $requestData = ['customer' => $customerDataArray, 'password' => CustomerHelper::PASSWORD];
        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Expected exception did not occur.');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
                $expectedException = new InputException();
                $expectedException->addError(
                    InputException::INVALID_FIELD_VALUE,
                    ['fieldName' => 'email', 'value' => $invalidEmail]
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
                    'message' => InputException::INVALID_FIELD_VALUE,
                    'parameters' => [
                        'fieldName' => 'email',
                        'value' => $invalidEmail,
                    ],
                ];
                $this->assertEquals($expectedExceptionData, $exceptionData);
            }
        }
    }

    /**
     * Test customer activation when it is required
     *
     * @magentoConfigFixture default_store customer/create_account/confirm 0
     */
    public function testActivateCustomer()
    {
        $customerData = $this->_createCustomer();
        $this->assertNotNull($customerData[Customer::CONFIRMATION], 'Customer activation is not required');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerData[Customer::EMAIL] . '/activate',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Activate',
            ],
        ];

        $requestData = [
            'email' => $customerData[Customer::EMAIL],
            'confirmationKey' => $customerData[Customer::CONFIRMATION],
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals($customerData[Customer::ID], $result[Customer::ID], 'Wrong customer!');
        $this->assertTrue(
            !isset($result[Customer::CONFIRMATION]) || $result[Customer::CONFIRMATION] === null,
            'Customer is not activated!'
        );
    }

    public function testGetCustomerActivateCustomer()
    {
        $customerData = $this->_createCustomer();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerData[Customer::EMAIL] . '/activate',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Activate',
            ],
        ];
        $requestData = [
            'email' => $customerData[Customer::EMAIL],
            'confirmationKey' => $customerData[Customer::CONFIRMATION],
        ];

        $customerResponseData = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals($customerData[Customer::ID], $customerResponseData[Customer::ID]);
        // Confirmation key is removed after confirmation
        $this->assertFalse(isset($customerResponseData[Customer::CONFIRMATION]));
    }

    public function testValidateResetPasswordLinkToken()
    {
        $customerData = $this->_createCustomer();
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = Bootstrap::getObjectManager()->create('Magento\Customer\Model\CustomerFactory')
            ->create();
        $customerModel->load($customerData[Customer::ID]);
        $rpToken = 'lsdj579slkj5987slkj595lkj';
        $customerModel->setRpToken('lsdj579slkj5987slkj595lkj');
        $customerModel->setRpTokenCreatedAt(date('Y-m-d'));
        $customerModel->save();
        $path = self::RESOURCE_PATH . '/' . $customerData[Customer::ID] . '/password/resetLinkToken/' . $rpToken;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $path,
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'ValidateResetPasswordLinkToken',
            ],
        ];

        $this->_webApiCall(
            $serviceInfo,
            ['customerId' => $customerData['id'], 'resetPasswordLinkToken' => $rpToken]
        );
    }

    public function testValidateResetPasswordLinkTokenInvalidToken()
    {
        $customerData = $this->_createCustomer();
        $invalidToken = 'fjjkafjie';
        $path = self::RESOURCE_PATH . '/' . $customerData[Customer::ID] . '/password/resetLinkToken/' . $invalidToken;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $path,
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'ValidateResetPasswordLinkToken',
            ],
        ];

        $expectedMessage = 'Reset password token mismatch.';

        try {
            if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
                $this->_webApiCall(
                    $serviceInfo,
                    ['customerId' => $customerData['id'], 'resetPasswordLinkToken' => 'invalid']
                );
            } else {
                $this->_webApiCall($serviceInfo);
            }
            $this->fail("Expected exception to be thrown.");
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "Exception message does not match"
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
        }
    }

    public function testInitiatePasswordMissingRequiredFields()
    {
        $this->_markTestAsRestOnly('Soap clients explicitly check for required fields based on WSDL.');
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/password',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ]
        ];

        try {
            $this->_webApiCall($serviceInfo);
        } catch (\Exception $e) {
            $this->assertEquals(\Magento\Webapi\Exception::HTTP_BAD_REQUEST, $e->getCode());
            $exceptionData = $this->processRestExceptionResult($e);
            $expectedExceptionData = [
                'message' => InputException::DEFAULT_MESSAGE,
                'errors' => [
                    [
                        'message' => InputException::REQUIRED_FIELD,
                        'parameters' => [
                            'fieldName' => 'email',
                        ],
                    ],
                    [
                        'message' => InputException::REQUIRED_FIELD,
                        'parameters' => [
                            'fieldName' => 'template',
                        ]
                    ],
                ],
            ];
            $this->assertEquals($expectedExceptionData, $exceptionData);
        }
    }

    public function testInitiatePasswordReset()
    {
        $customerData = $this->_createCustomer();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/password',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'InitiatePasswordReset',
            ],
        ];
        $requestData = [
            'email' => $customerData[Customer::EMAIL],
            'template' => AccountManagement::EMAIL_RESET,
            'websiteId' => $customerData[Customer::WEBSITE_ID],
        ];
        // This api doesn't return any response.
        // No exception or response means the request was processed successfully.
        // The webapi framework does not return the header information as yet. A check for HTTP 200 would be ideal here
        $this->_webApiCall($serviceInfo, $requestData);
    }

    public function testSendPasswordResetLinkBadEmailOrWebsite()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/password',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'InitiatePasswordReset',
            ],
        ];
        $requestData = [
            'email' => 'dummy@example.com',
            'template' => AccountManagement::EMAIL_RESET,
            'websiteId' => 0,
        ];
        try {
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\Exception $e) {
            $expectedErrorParameters =
                [
                    'fieldName' => 'email',
                    'fieldValue' => 'dummy@example.com',
                    'field2Name' => 'websiteId',
                    'field2Value' => 0,
                ];
            if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
                $errorObj = $this->processRestExceptionResult($e);
                $this->assertEquals(
                    NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                    $errorObj['message']
                );
                $this->assertEquals($expectedErrorParameters, $errorObj['parameters']);
                $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
            } else {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault(
                    $e,
                    NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                    'env:Sender',
                    $expectedErrorParameters
                );
            }
        }
    }

    public function testGetConfirmationStatus()
    {
        $customerData = $this->_createCustomer();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerData[Customer::ID] . '/confirm',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetConfirmationStatus',
            ],
        ];

        $confirmationResponse = $this->_webApiCall($serviceInfo, ['customerId' => $customerData['id']]);

        $this->assertEquals(AccountManagement::ACCOUNT_CONFIRMATION_NOT_REQUIRED, $confirmationResponse);
    }

    public function testResendConfirmation()
    {
        $customerData = $this->_createCustomer();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/confirm',
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'ResendConfirmation',
            ],
        ];
        $requestData = [
            'email' => $customerData[Customer::EMAIL],
            'websiteId' => $customerData[Customer::WEBSITE_ID],
        ];
        // This api doesn't return any response.
        // No exception or response means the request was processed successfully.
        // The webapi framework does not return the header information as yet. A check for HTTP 200 would be ideal here
        $this->_webApiCall($serviceInfo, $requestData);
    }

    public function testResendConfirmationBadEmailOrWebsite()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/confirm',
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'ResendConfirmation',
            ],
        ];
        $requestData = [
            'email' => 'dummy@example.com',
            'websiteId' => 0,
        ];
        try {
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\Exception $e) {
            $expectedErrorParameters =
                [
                    'fieldName' => 'email',
                    'fieldValue' => 'dummy@example.com',
                    'field2Name' => 'websiteId',
                    'field2Value' => 0,
                ];
            if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
                $errorObj = $this->processRestExceptionResult($e);
                $this->assertEquals(
                    NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                    $errorObj['message']
                );
                $this->assertEquals($expectedErrorParameters, $errorObj['parameters']);
                $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
            } else {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault(
                    $e,
                    NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                    'env:Sender',
                    $expectedErrorParameters
                );
            }
        }
    }

    public function testValidateCustomerData()
    {
        $customerData = $this->customerHelper->createSampleCustomerDataObject();
        $customerData = $this->customerBuilder->populate($customerData)
            ->setFirstname(null)->setLastname(null)->create();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/validate',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Validate',
            ],
        ];
        $customerData = $this->dataObjectProcessor->buildOutputDataArray(
            $customerData,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $requestData = ['customer' => $customerData];
        $validationResponse = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertFalse($validationResponse['valid']);
        $this->assertEquals('The first name cannot be empty.', $validationResponse['messages'][0]);
        $this->assertEquals('The last name cannot be empty.', $validationResponse['messages'][1]);
    }

    public function testIsReadonly()
    {
        $customerData = $this->_createCustomer();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerData[Customer::ID] . '/permissions/readonly',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'IsReadonly',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['customerId' => $customerData['id']]);

        $this->assertFalse($response);
    }

    public function testEmailAvailable()
    {
        $customerData = $this->_createCustomer();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/isEmailAvailable',
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'IsEmailAvailable',
            ],
        ];
        $requestData = [
            'customerEmail' => $customerData[Customer::EMAIL],
            'websiteId' => $customerData[Customer::WEBSITE_ID],
        ];
        $this->assertFalse($this->_webApiCall($serviceInfo, $requestData));
    }

    public function testEmailAvailableInvalidEmail()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/isEmailAvailable',
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'IsEmailAvailable',
            ],
        ];
        $requestData = [
            'customerEmail' => 'invalid',
            'websiteId' => 0,
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/attribute_user_defined_address.php
     * @magentoApiDataFixture Magento/Customer/_files/attribute_user_defined_customer.php
     */
    public function testCustomAttributes()
    {
        //Sample customer data comes with the disable_auto_group_change custom attribute
        $customerData = $this->customerHelper->createSampleCustomerDataObject();
        //address attribute code from fixture
        $fixtureAddressAttributeCode = 'address_user_attribute';
        //customer attribute code from fixture
        $fixtureCustomerAttributeCode = 'user_attribute';
        //Custom Attribute Values
        $address1CustomAttributeValue = 'value1';
        $address2CustomAttributeValue = 'value2';
        $customerCustomAttributeValue = 'value3';

        $addresses = $customerData->getAddresses();
        $address1 = $this->addressBuilder
            ->populate($addresses[0])
            ->setCustomAttribute($fixtureAddressAttributeCode, $address1CustomAttributeValue)
            ->create();
        $address2 = $this->addressBuilder
            ->populate($addresses[1])
            ->setCustomAttribute($fixtureAddressAttributeCode, $address2CustomAttributeValue)
            ->create();

        $customer = $this->customerBuilder
            ->populate($customerData)
            ->setAddresses([$address1, $address2])
            ->setCustomAttribute($fixtureCustomerAttributeCode, $customerCustomAttributeValue)
            ->create();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'CreateAccount',
            ],
        ];

        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $customer,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $requestData = ['customer' => $customerDataArray, 'password' => CustomerHelper::PASSWORD];
        $customerData = $this->_webApiCall($serviceInfo, $requestData);
        $customerId = $customerData['id'];
        //TODO: Fix assertions to verify custom attributes
        $this->assertNotNull($customerData);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerId ,
                'httpMethod' => RestConfig::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => CustomerRepositoryTest::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => CustomerRepositoryTest::SERVICE_NAME . 'DeleteById',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['customerId' => $customerId]);
        $this->assertTrue($response);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testGetDefaultBillingAddress()
    {
        $fixtureCustomerId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$fixtureCustomerId/billingAddress",
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetDefaultBillingAddress',
            ],
        ];
        $requestData = ['customerId' => $fixtureCustomerId];
        $addressData = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(
            $this->getFirstFixtureAddressData(),
            $addressData,
            "Default billing address data is invalid."
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testGetDefaultShippingAddress()
    {
        $fixtureCustomerId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$fixtureCustomerId/shippingAddress",
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetDefaultShippingAddress',
            ],
        ];
        $requestData = ['customerId' => $fixtureCustomerId];
        $addressData = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(
            $this->getFirstFixtureAddressData(),
            $addressData,
            "Default shipping address data is invalid."
        );
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

    /**
     * Retrieve data of the first fixture address.
     *
     * @return array
     */
    protected function getFirstFixtureAddressData()
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'city' => 'CityM',
            'country_id' => 'US',
            'company' => 'CompanyName',
            'postcode' => '75477',
            'telephone' => '3468676',
            'street' => ['Green str, 67'],
            'id' => 1,
            'default_billing' => true,
            'default_shipping' => true,
            'customer_id' => '1',
            'region' => ['region' => 'Alabama', 'region_id' => 1, 'region_code' => 'AL'],
        ];
    }
}
