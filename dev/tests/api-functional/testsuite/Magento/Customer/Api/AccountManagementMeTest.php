<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;

/**
 * Class AccountManagementMeTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoApiDataFixture Magento/Customer/_files/customer.php
 * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
 */
class AccountManagementMeTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const RESOURCE_PATH = '/V1/customers/me';
    const RESOURCE_PATH_CUSTOMER_TOKEN = "/V1/integration/customer/token";

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

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
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->_markTestAsRestOnly();

        $this->customerRegistry = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Model\CustomerRegistry'
        );

        $this->customerRepository = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\CustomerRepositoryInterface',
            ['customerRegistry' => $this->customerRegistry]
        );

        $this->customerAccountManagement = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Api\AccountManagementInterface');

        $this->customerHelper = new CustomerHelper();
        $this->customerData = $this->customerHelper->createSampleCustomer();

        // get token
        $this->resetTokenForCustomerSampleData();

        $this->dataObjectProcessor = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Reflection\DataObjectProcessor'
        );
    }

    /**
     * Ensure that fixture customer and his addresses are deleted.
     */
    public function tearDown()
    {
        unset($this->customerRepository);

        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
        parent::tearDown();
    }

    public function testChangePassword()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/password',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
                'token' => $this->token,
            ],
        ];
        $requestData = ['currentPassword' => 'test@123', 'newPassword' => '123@test'];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));

        $customerResponseData = $this->customerAccountManagement
            ->authenticate($this->customerData[CustomerInterface::EMAIL], '123@test');
        $this->assertEquals($this->customerData[CustomerInterface::ID], $customerResponseData->getId());
    }

    public function testUpdateCustomer()
    {
        $customerData = $this->_getCustomerData($this->customerData[CustomerInterface::ID]);
        $lastName = $customerData->getLastname();

        $updatedCustomerData = $this->dataObjectProcessor->buildOutputDataArray(
            $customerData,
            'Magento\Customer\Api\Data\CustomerInterface'
        );
        $updatedCustomerData[CustomerInterface::LASTNAME] = $lastName . 'Updated';
        $updatedCustomerData[CustomerInterface::ID] = 25;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
                'token' => $this->token,
            ],
        ];
        $requestData = ['customer' => $updatedCustomerData];

        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($lastName . "Updated", $response[CustomerInterface::LASTNAME]);

        $customerData = $this->_getCustomerData($this->customerData[CustomerInterface::ID]);
        $this->assertEquals($lastName . "Updated", $customerData->getLastname());
    }

    public function testGetCustomerData()
    {
        //Get expected details from the Service directly
        $customerData = $this->_getCustomerData($this->customerData[CustomerInterface::ID]);
        $expectedCustomerDetails = $this->dataObjectProcessor->buildOutputDataArray(
            $customerData,
            'Magento\Customer\Api\Data\CustomerInterface'
        );
        $expectedCustomerDetails['addresses'][0]['id'] =
            (int)$expectedCustomerDetails['addresses'][0]['id'];

        $expectedCustomerDetails['addresses'][1]['id'] =
            (int)$expectedCustomerDetails['addresses'][1]['id'];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $this->token,
            ],
        ];
        $customerDetailsResponse = $this->_webApiCall($serviceInfo);

        unset($expectedCustomerDetails['custom_attributes']);
        unset($customerDetailsResponse['custom_attributes']); //for REST

        $this->assertEquals($expectedCustomerDetails, $customerDetailsResponse);
    }

    public function testGetCustomerActivateCustomer()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/activate',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
                'token' => $this->token,
            ],
        ];
        $requestData = ['confirmationKey' => $this->customerData[CustomerInterface::CONFIRMATION]];
        $customerResponseData = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($this->customerData[CustomerInterface::ID], $customerResponseData[CustomerInterface::ID]);
        // Confirmation key is removed after confirmation
        $this->assertFalse(isset($customerResponseData[CustomerInterface::CONFIRMATION]));
    }

    /**
     * Return the customer details.
     *
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function _getCustomerData($customerId)
    {
        $data = $this->customerRepository->getById($customerId);
        $this->customerRegistry->remove($customerId);
        return $data;
    }

    public function testGetDefaultBillingAddress()
    {
        $this->resetTokenForCustomerFixture();

        $fixtureCustomerId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/customers/me/billingAddress",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $this->token,
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

    public function testGetDefaultShippingAddress()
    {
        $this->resetTokenForCustomerFixture();

        $fixtureCustomerId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/customers/me/shippingAddress",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $this->token,
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

    /**
     * Retrieve data of the second fixture address.
     *
     * @return array
     */
    protected function getSecondFixtureAddressData()
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'city' => 'CityX',
            'country_id' => 'US',
            'postcode' => '47676',
            'telephone' => '3234676',
            'street' => ['Black str, 48'],
            'id' => 2,
            'default_billing' => false,
            'default_shipping' => false,
            'customer_id' => '1',
            'region' => ['region' => 'Alabama', 'region_id' => 1, 'region_code' => 'AL'],
            'region_id' => 1,
        ];
    }

    /**
     * Sets the test's access token for the customer fixture
     */
    protected function resetTokenForCustomerFixture()
    {
        $this->resetTokenForCustomer('customer@example.com', 'password');
    }

    /**
     * Sets the test's access token for the created customer sample data
     */
    protected function resetTokenForCustomerSampleData()
    {
        $this->resetTokenForCustomer($this->customerData[CustomerInterface::EMAIL], 'test@123');
    }

    /**
     * Sets the test's access token for a particular username and password.
     *
     * @param string $username
     * @param string $password
     */
    protected function resetTokenForCustomer($username, $password)
    {
        // get customer ID token
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = ['username' => $username, 'password' => $password];
        $this->token = $this->_webApiCall($serviceInfo, $requestData);
    }
}
