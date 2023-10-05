<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface as Customer;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;
use Magento\Newsletter\Model\Subscriber;
use Magento\Security\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test class for Magento\Customer\Api\AccountManagementInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountManagementTest extends WebapiAbstract
{
    public const SERVICE_VERSION = 'V1';
    public const SERVICE_NAME = 'customerAccountManagementV1';
    public const RESOURCE_PATH = '/V1/customers';

    /**
     * Sample values for testing
     */
    public const ATTRIBUTE_CODE = 'attribute_code';
    public const ATTRIBUTE_VALUE = 'attribute_value';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

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

    /** @var  Subscriber */
    private $subscriber;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var \Magento\Config\Model\Config
     */
    private $config;

    /**
     * @var int
     */
    private $configValue;

    /**
     * Execute per test initialization.
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->accountManagement = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->sortOrderBuilder = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Api\SortOrderBuilder::class
        );
        $this->filterGroupBuilder = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Api\Search\FilterGroupBuilder::class
        );
        $this->customerHelper = new CustomerHelper();

        $this->dataObjectProcessor = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Reflection\DataObjectProcessor::class
        );
        $this->config = Bootstrap::getObjectManager()->create(
            \Magento\Config\Model\Config::class
        );
        $this->initSubscriber();

        if ($this->config->getConfigDataValue(
            Config::XML_PATH_FRONTEND_AREA .
            Config::XML_PATH_PASSWORD_RESET_PROTECTION_TYPE
        ) != 0) {
            $this->configValue = $this->config
                ->getConfigDataValue(
                    Config::XML_PATH_FRONTEND_AREA .
                    Config::XML_PATH_PASSWORD_RESET_PROTECTION_TYPE
                );
            $this->config->setDataByPath(
                Config::XML_PATH_FRONTEND_AREA . Config::XML_PATH_PASSWORD_RESET_PROTECTION_TYPE,
                0
            );
            $this->config->save();
        }
    }

    protected function tearDown(): void
    {
        if (!empty($this->currentCustomerId)) {
            foreach ($this->currentCustomerId as $customerId) {
                $serviceInfo = [
                    'rest' => [
                        'resourcePath' => self::RESOURCE_PATH . '/' . $customerId,
                        'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
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
        $this->config->setDataByPath(
            Config::XML_PATH_FRONTEND_AREA . Config::XML_PATH_PASSWORD_RESET_PROTECTION_TYPE,
            $this->configValue
        );
        $this->config->save();
        $this->accountManagement = null;
        $this->subscriber = null;
    }

    private function initSubscriber()
    {
        $this->subscriber = Bootstrap::getObjectManager()->create(
            \Magento\Newsletter\Model\Subscriber::class
        );
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST, ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'CreateAccount',
            ],
        ];

        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerHelper->createSampleCustomerDataObject(),
            \Magento\Customer\Api\Data\CustomerInterface::class
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
                $expectedException->addError(__('"Email" is not a valid email address.'));
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
                    'message' => '"Email" is not a valid email address.',
                ];
                $this->assertEquals($expectedExceptionData, $exceptionData);
            }
        }
    }

    public function testCreateCustomerWithoutOptionalFields()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST, ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'CreateAccount',
            ],
        ];

        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerHelper->createSampleCustomerDataObject(),
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        unset($customerDataArray['store_id']);
        unset($customerDataArray['website_id']);
        $requestData = ['customer' => $customerDataArray, 'password' => CustomerHelper::PASSWORD];
        try {
            $customerData = $this->_webApiCall($serviceInfo, $requestData, null, 'all');
            $this->assertNotNull($customerData['id']);
        } catch (\Exception $e) {
            $this->fail('Customer should be created without optional fields.');
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

        // Update the customer's confirmation key to a known value
        $customerData = $this->customerHelper->updateSampleCustomer(
            $customerData[Customer::ID],
            [
                'id' => $customerData[Customer::ID],
                'confirmation' => CustomerHelper::CONFIRMATION
            ]
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerData[Customer::EMAIL] . '/activate',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Activate',
            ],
        ];

        $requestData = [
            'email' => $customerData[Customer::EMAIL],
            'confirmationKey' => CustomerHelper::CONFIRMATION
        ];

        try {
            $result = $this->_webApiCall($serviceInfo, $requestData);
            $this->assertEquals($customerData[Customer::ID], $result[Customer::ID], 'Wrong customer!');
        } catch (\Exception $e) {
            $this->fail('Customer is not activated.');
        }
    }

    public function testGetCustomerActivateCustomer()
    {
        $customerData = $this->_createCustomer();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerData[Customer::EMAIL] . '/activate',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Activate',
            ],
        ];
        $requestData = [
            'email' => $customerData[Customer::EMAIL],
            'confirmationKey' => CustomerHelper::CONFIRMATION
        ];

        try {
            $customerResponseData = $this->_webApiCall($serviceInfo, $requestData);
            $this->assertEquals($customerData[Customer::ID], $customerResponseData[Customer::ID]);
        } catch (\Exception $e) {
            $this->fail('Customer is not activated.');
        }
    }

    public function testValidateResetPasswordLinkToken()
    {
        $customerData = $this->_createCustomer();
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = Bootstrap::getObjectManager()->create(\Magento\Customer\Model\CustomerFactory::class)
            ->create();
        $customerModel->load($customerData[Customer::ID]);
        $rpToken = 'lsdj579slkj5987slkj595lkj';
        $customerModel->setRpToken('lsdj579slkj5987slkj595lkj');
        $customerModel->setRpTokenCreatedAt(date('Y-m-d H:i:s'));
        $customerModel->save();
        $path = self::RESOURCE_PATH . '/' . $customerData[Customer::ID] . '/password/resetLinkToken/' . $rpToken;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $path,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'ValidateResetPasswordLinkToken',
            ],
        ];

        $expectedMessage = 'The password token is mismatched. Reset and try again.';

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
            $this->assertStringContainsString(
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ]
        ];

        try {
            $this->_webApiCall($serviceInfo);
        } catch (\Exception $e) {
            $this->assertEquals(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST, $e->getCode());
            $exceptionData = $this->processRestExceptionResult($e);
            $expectedExceptionData = [
                'message' => 'One or more input exceptions have occurred.',
                'errors' => [
                    [
                        'message' => '"%fieldName" is required. Enter and try again.',
                        'parameters' => [
                            'fieldName' => 'email',
                        ],
                    ],
                    [
                        'message' => '"%fieldName" is required. Enter and try again.',
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
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
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    $errorObj['message']
                );
                $this->assertEquals($expectedErrorParameters, $errorObj['parameters']);
                $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
            } else {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault(
                    $e,
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
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
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    $errorObj['message']
                );
                $this->assertEquals($expectedErrorParameters, $errorObj['parameters']);
                $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
            } else {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault(
                    $e,
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    'env:Sender',
                    $expectedErrorParameters
                );
            }
        }
    }

    public function testValidateCustomerData()
    {
        $customerData = $this->customerHelper->createSampleCustomerDataObject();
        $customerData->setFirstname(null);
        $customerData->setLastname(null);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/validate',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Validate',
            ],
        ];
        $customerData = $this->dataObjectProcessor->buildOutputDataArray(
            $customerData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $requestData = ['customer' => $customerData];
        $validationResponse = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertFalse($validationResponse['valid']);

        $this->assertEquals(
            'The "First Name" attribute value is empty. Set the attribute and try again.',
            $validationResponse['messages'][0]
        );
        $this->assertEquals(
            'The "Last Name" attribute value is empty. Set the attribute and try again.',
            $validationResponse['messages'][1]
        );
    }

    public function testIsReadonly()
    {
        $customerData = $this->_createCustomer();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerData[Customer::ID] . '/permissions/readonly',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
        $config = $this->objectManager->get(ScopeConfigInterface::class);

        $customerData = $this->_createCustomer();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/isEmailAvailable',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
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

        $emailSetting = $config->getValue(
            AccountManagement::GUEST_CHECKOUT_LOGIN_OPTION_SYS_CONFIG,
            ScopeInterface::SCOPE_WEBSITE,
            $customerData[Customer::WEBSITE_ID]
        );

        if (!$emailSetting) {
            $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
        } else {
            $this->assertFalse($this->_webApiCall($serviceInfo, $requestData));
        }
    }

    public function testEmailAvailableInvalidEmail()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/isEmailAvailable',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
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
        $addresses[0]->setCustomAttribute($fixtureAddressAttributeCode, $address1CustomAttributeValue);
        $addresses[1]->setCustomAttribute($fixtureAddressAttributeCode, $address2CustomAttributeValue);
        $customerData->setAddresses($addresses);
        $customerData->setCustomAttribute($fixtureCustomerAttributeCode, $customerCustomAttributeValue);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'CreateAccount',
            ],
        ];

        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $customerData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $requestData = ['customer' => $customerDataArray, 'password' => CustomerHelper::PASSWORD];
        $customerData = $this->_webApiCall($serviceInfo, $requestData);
        $customerId = $customerData['id'];
        //TODO: Fix assertions to verify custom attributes
        $this->assertNotNull($customerData);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customerId ,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
            'region_id' => 1,
        ];
    }

    public function testCreateCustomerWithSubscription()
    {
        $customerData = $this->customerHelper->createSampleCustomer(
            ["extension_attributes" => ["is_subscribed" => true]]
        );

        $this->assertNotNull($customerData['id']);

        $this->subscriber->loadByCustomerId($customerData['id']);

        $this->assertNotNull($this->subscriber->getId());
        $this->assertEquals($customerData['id'], $this->subscriber->getCustomerId());
    }

    public function testUnsubscribeCustomer()
    {
        //Creating customer and subscribe
        $customerData = $this->customerHelper->createSampleCustomer(
            ["extension_attributes" => ["is_subscribed" => true]]
        );
        $this->assertNotNull($customerData['id']);

        $this->subscriber->loadByCustomerId($customerData['id']);
        $subscriptionId = $this->subscriber->getId();

        $this->assertNotNull($subscriptionId);
        $this->assertEquals($customerData['id'], $this->subscriber->getCustomerId());
        //Manage customer in order to unsubscribe
        $this->customerHelper->updateSampleCustomer(
            $customerData["id"],
            array_merge(
                $customerData,
                ["extension_attributes" => ["is_subscribed" => false]]
            )
        );
        $this->initSubscriber();

        $this->subscriber->loadByCustomerId($customerData['id']);
        $this->assertEquals(Subscriber::STATUS_UNSUBSCRIBED, $this->subscriber->getStatus());
    }
}
