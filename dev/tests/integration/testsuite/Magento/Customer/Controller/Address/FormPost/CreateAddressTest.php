<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Address\FormPost;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Directory\Model\GetRegionIdByName;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test cases related to check that customer address correctly created from
 * customer account page on frontend or wasn't create and proper error message appears.
 *
 * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 *
 * @see \Magento\Customer\Controller\Address\FormPost::execute
 */
class CreateAddressTest extends AbstractController
{
    /**
     * POST static data for create customer address via controller on frontend.
     */
    private const STATIC_POST_ADDRESS_DATA = [
        AddressInterface::TELEPHONE => '+380505282812',
        AddressInterface::POSTCODE => 75477,
        AddressInterface::COUNTRY_ID => 'US',
        'custom_region_name' => 'Alabama',
        AddressInterface::CITY => 'CityM',
        AddressInterface::STREET => [
            'Green str, 67',
        ],
        AddressInterface::FIRSTNAME => 'John',
        AddressInterface::LASTNAME => 'Smith',
    ];

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var GetRegionIdByName
     */
    private $getRegionIdByName;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->customerRegistry = $this->_objectManager->get(CustomerRegistry::class);
        $this->getRegionIdByName = $this->_objectManager->get(GetRegionIdByName::class);
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $this->customerSession->setCustomerId('5');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->customerSession->setCustomerId(null);
        $this->customerRegistry->removeByEmail('customer5@example.com');
        parent::tearDown();
    }

    /**
     * Assert that default or non-default customer address successfully created via controller on frontend.
     *
     * @dataProvider postDataForSuccessCreateDefaultAddressDataProvider
     *
     * @param array $postData
     * @param bool $isShippingDefault
     * @param bool $isBillingDefault
     * @return void
     */
    public function testAddressSuccessfullyCreatedAsDefaultForCustomer(
        array $postData,
        bool $isShippingDefault,
        bool $isBillingDefault
    ): void {
        $customer = $this->customerRepository->get('customer5@example.com');
        $this->assertNull($customer->getDefaultShipping(), 'Customer already have default shipping address');
        $this->assertNull($customer->getDefaultBilling(), 'Customer already have default billing address');
        $this->assertEmpty($customer->getAddresses(), 'Customer already has address');
        $this->performRequestWithData($postData);
        $this->checkRequestPerformedSuccessfully();
        $customer = $this->customerRepository->get('customer5@example.com');
        $customerAddresses = $customer->getAddresses();
        $this->assertCount(1, $customerAddresses);
        /** @var AddressInterface $address */
        $address = reset($customerAddresses);
        $expectedShippingId = $isShippingDefault ? $address->getId() : null;
        $expectedBillingId = $isBillingDefault ? $address->getId() : null;
        $this->assertEquals($expectedShippingId, $customer->getDefaultShipping());
        $this->assertEquals($expectedBillingId, $customer->getDefaultBilling());
    }

    /**
     * Data provider which contain proper POST data for create default or non-default customer address.
     *
     * @return array
     */
    public function postDataForSuccessCreateDefaultAddressDataProvider(): array
    {
        return [
            'any_addresses_are_default' => [
                array_merge(
                    self::STATIC_POST_ADDRESS_DATA,
                    [AddressInterface::DEFAULT_SHIPPING => 0, AddressInterface::DEFAULT_BILLING => 0]
                ),
                false,
                false,
            ],
            'shipping_address_is_default' => [
                array_merge(
                    self::STATIC_POST_ADDRESS_DATA,
                    [AddressInterface::DEFAULT_SHIPPING => 1, AddressInterface::DEFAULT_BILLING => 0]
                ),
                true,
                false,
            ],
            'billing_address_is_default' => [
                array_merge(
                    self::STATIC_POST_ADDRESS_DATA,
                    [AddressInterface::DEFAULT_SHIPPING => 0, AddressInterface::DEFAULT_BILLING => 1]
                ),
                false,
                true,
            ],
            'all_addresses_are_default' => [
                array_merge(
                    self::STATIC_POST_ADDRESS_DATA,
                    [AddressInterface::DEFAULT_SHIPPING => 1, AddressInterface::DEFAULT_BILLING => 1]
                ),
                true,
                true,
            ],
        ];
    }

    /**
     * Assert that customer address successfully created via controller on frontend.
     *
     * @dataProvider postDataForSuccessCreateAddressDataProvider
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    public function testAddressSuccessfullyCreatedForCustomer(array $postData, array $expectedData): void
    {
        if (isset($expectedData['custom_region_name'])) {
            $expectedData[AddressInterface::REGION_ID] = $this->getRegionIdByName->execute(
                $expectedData['custom_region_name'],
                $expectedData[AddressInterface::COUNTRY_ID]
            );
            unset($expectedData['custom_region_name']);
        }
        $this->performRequestWithData($postData);
        $this->checkRequestPerformedSuccessfully();
        $customer = $this->customerRepository->get('customer5@example.com');
        $customerAddresses = $customer->getAddresses();
        $this->assertCount(1, $customerAddresses);
        /** @var AddressInterface $address */
        $address = reset($customerAddresses);
        $createdAddressData = $address->__toArray();
        foreach ($expectedData as $fieldCode => $expectedValue) {
            $this->assertArrayHasKey($fieldCode, $createdAddressData, "Field $fieldCode wasn't found.");
            $this->assertEquals($expectedValue, $createdAddressData[$fieldCode]);
        }
    }

    /**
     * Data provider which contain proper POST data for create customer address.
     *
     * @return array
     */
    public function postDataForSuccessCreateAddressDataProvider(): array
    {
        return [
            'required_fields_valid_data' => [
                self::STATIC_POST_ADDRESS_DATA,
                [
                    AddressInterface::TELEPHONE => '+380505282812',
                    AddressInterface::COUNTRY_ID => 'US',
                    AddressInterface::POSTCODE => 75477,
                    'custom_region_name' => 'Alabama',
                    AddressInterface::FIRSTNAME => 'John',
                    AddressInterface::LASTNAME => 'Smith',
                    AddressInterface::STREET => ['Green str, 67'],
                    AddressInterface::CITY => 'CityM',
                ],
            ],
            'required_field_empty_postcode_for_uk' => [
                array_replace(
                    self::STATIC_POST_ADDRESS_DATA,
                    [AddressInterface::POSTCODE => '', AddressInterface::COUNTRY_ID => 'GB']
                ),
                [
                    AddressInterface::COUNTRY_ID => 'GB',
                    AddressInterface::POSTCODE => null,
                ],
            ],
            'required_field_empty_region_id_for_ua' => [
                array_replace(
                    self::STATIC_POST_ADDRESS_DATA,
                    [AddressInterface::REGION_ID => '', AddressInterface::COUNTRY_ID => 'UA']
                ),
                [
                    AddressInterface::COUNTRY_ID => 'UA',
                    AddressInterface::REGION => [
                        RegionInterface::REGION => null,
                        RegionInterface::REGION_CODE => null,
                        RegionInterface::REGION_ID => 0,
                    ],
                ],
            ],
            'required_field_street_as_array' => [
                array_replace(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::STREET => ['', 'Green str, 67']]),
                [AddressInterface::STREET => ['Green str, 67']],
            ],
            'field_company_name' => [
                array_merge(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::COMPANY => 'My company']),
                [AddressInterface::COMPANY => 'My company'],
            ],
            'field_vat_number' => [
                array_merge(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::VAT_ID => 'My VAT number']),
                [AddressInterface::VAT_ID => 'My VAT number'],
            ],
        ];
    }

    /**
     * Assert that customer address wasn't created via controller on frontend
     * when POST data broken.
     *
     * @dataProvider postDataForCreateAddressWithErrorDataProvider
     *
     * @param array $postData
     * @param array $expectedSessionMessages
     * @return void
     */
    public function testAddressWasntCreatedForCustomer(array $postData, array $expectedSessionMessages): void
    {
        $this->performRequestWithData($postData);
        $this->checkRequestPerformedWithInputValidationErrors($expectedSessionMessages);
    }

    /**
     * Data provider which contain broken POST data for create customer address with error.
     *
     * @return array
     */
    public function postDataForCreateAddressWithErrorDataProvider(): array
    {
        return [
            'empty_post_data' => [
                [],
                [
                    'One or more input exceptions have occurred.',
                    '"firstname" is required. Enter and try again.',
                    '"lastname" is required. Enter and try again.',
                    '"street" is required. Enter and try again.',
                    '"city" is required. Enter and try again.',
                    '"telephone" is required. Enter and try again.',
                    '"postcode" is required. Enter and try again.',
                    '"countryId" is required. Enter and try again.',
                ]
            ],
            'required_field_empty_telephone' => [
                array_replace(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::TELEPHONE => '']),
                ['"telephone" is required. Enter and try again.'],
            ],
            'required_field_empty_postcode_for_us' => [
                array_replace(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::POSTCODE => '']),
                ['"postcode" is required. Enter and try again.'],
            ],
// TODO: Uncomment this variation after fix issue https://jira.corp.magento.com/browse/MC-31031
//            'required_field_empty_region_id_for_us' => [
//                array_replace(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::REGION_ID => '']),
//                ['"regionId" is required. Enter and try again.'],
//            ],
            'required_field_empty_firstname' => [
                array_replace(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::FIRSTNAME => '']),
                ['"firstname" is required. Enter and try again.'],
            ],
            'required_field_empty_lastname' => [
                array_replace(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::LASTNAME => '']),
                ['"lastname" is required. Enter and try again.'],
            ],
            'required_field_empty_street_as_string' => [
                array_replace(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::STREET => '']),
                ['"street" is required. Enter and try again.'],
            ],
            'required_field_empty_street_as_array' => [
                array_replace(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::STREET => []]),
                ['"street" is required. Enter and try again.'],
            ],
            'required_field_empty_city' => [
                array_replace(self::STATIC_POST_ADDRESS_DATA, [AddressInterface::CITY => '']),
                ['"city" is required. Enter and try again.'],
            ],
        ];
    }

    /**
     * Perform request with provided POST data.
     *
     * @param array $postData
     * @return void
     */
    private function performRequestWithData(array $postData): void
    {
        if (isset($postData['custom_region_name'])) {
            $postData[AddressInterface::REGION_ID] = $this->getRegionIdByName->execute(
                $postData['custom_region_name'],
                $postData[AddressInterface::COUNTRY_ID]
            );
            unset($postData['custom_region_name']);
        }

        $this->getRequest()->setPostValue($postData)->setMethod(Http::METHOD_POST);
        $this->dispatch('customer/address/formPost');
    }

    /**
     * Check that save address request performed successfully
     * (proper success message and redirect to customer/address/index are appear).
     *
     * @return void
     */
    private function checkRequestPerformedSuccessfully(): void
    {
        $this->assertRedirect($this->stringContains('customer/address/index'));
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the address.')]),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Check that save address request performed with input validation errors
     * (proper error messages and redirect to customer/address/edit are appear).
     *
     * @param array $expectedSessionMessages
     * @return void
     */
    private function checkRequestPerformedWithInputValidationErrors(array $expectedSessionMessages): void
    {
        $this->assertRedirect($this->stringContains('customer/address/edit'));
        foreach ($expectedSessionMessages as $expectedMessage) {
            $this->assertSessionMessages(
                $this->contains($this->escaper->escapeHtml((string)__($expectedMessage))),
                MessageInterface::TYPE_ERROR
            );
        }
    }
}
