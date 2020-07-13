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
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Directory\Model\GetRegionIdByName;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test cases related to check that customer address correctly updated from
 * customer account page on frontend or wasn't updated and proper error message appears.
 *
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @magentoDataFixture Magento/Customer/_files/customer_address.php
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 *
 * @see \Magento\Customer\Controller\Address\FormPost::execute
 */
class UpdateAddressTest extends AbstractController
{
    /**
     * POST static data for update customer address via controller on frontend.
     */
    private const STATIC_POST_ADDRESS_DATA = [
        AddressInterface::TELEPHONE => 9548642,
        AddressInterface::POSTCODE => 95556,
        AddressInterface::COUNTRY_ID => 'US',
        'custom_region_name' => 'Arkansas',
        AddressInterface::CITY => 'Mukachevo',
        AddressInterface::STREET => [
            'Yellow str, 228',
        ],
        AddressInterface::FIRSTNAME => 'Foma',
        AddressInterface::LASTNAME => 'Kiniaev',
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
     * @var AddressRegistry
     */
    private $addressRegistry;

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
     * @var array
     */
    private $processedAddressesIds = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->addressRegistry = $this->_objectManager->get(AddressRegistry::class);
        $this->customerRegistry = $this->_objectManager->get(CustomerRegistry::class);
        $this->getRegionIdByName = $this->_objectManager->get(GetRegionIdByName::class);
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $this->customerSession->setCustomerId('1');
        $this->processedAddressesIds[] = 1;
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->setCustomerId(null);
        $this->customerRegistry->removeByEmail('customer@example.com');
        foreach ($this->processedAddressesIds as $addressesId) {
            $this->addressRegistry->remove($addressesId);
        }
        parent::tearDown();
    }

    /**
     * Assert that default customer address successfully changed via controller on frontend.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     *
     * @dataProvider postDataForSuccessCreateDefaultAddressDataProvider
     *
     * @param array $postData
     * @param int $expectedShippingId
     * @param int $expectedBillingId
     * @return void
     */
    public function testAddressSuccessfullyCreatedAsDefaultForCustomer(
        array $postData,
        int $expectedShippingId,
        int $expectedBillingId
    ): void {
        $this->processedAddressesIds = [1, 2];
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertEquals(1, $customer->getDefaultShipping(), "Customer doesn't have shipping address");
        $this->assertEquals(1, $customer->getDefaultBilling(), "Customer doesn't have billing address");
        $this->performRequestWithData($postData, 2);
        $this->checkRequestPerformedSuccessfully();
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertEquals($expectedShippingId, $customer->getDefaultShipping());
        $this->assertEquals($expectedBillingId, $customer->getDefaultBilling());
    }

    /**
     * Data provider which contain proper POST data for change default customer address.
     *
     * @return array
     */
    public function postDataForSuccessCreateDefaultAddressDataProvider(): array
    {
        return [
            'any_addresses_are_default' => [
                array_merge(
                    self::STATIC_POST_ADDRESS_DATA,
                    [AddressInterface::DEFAULT_SHIPPING => 2, AddressInterface::DEFAULT_BILLING => 2]
                ),
                2,
                2,
            ],
            'shipping_address_is_default' => [
                array_merge(
                    self::STATIC_POST_ADDRESS_DATA,
                    [AddressInterface::DEFAULT_BILLING => 2]
                ),
                1,
                2,
            ],
            'billing_address_is_default' => [
                array_merge(
                    self::STATIC_POST_ADDRESS_DATA,
                    [AddressInterface::DEFAULT_SHIPPING => 2]
                ),
                2,
                1,
            ],
        ];
    }

    /**
     * Assert that customer address successfully updated via controller on frontend.
     *
     * @dataProvider postDataForSuccessUpdateAddressDataProvider
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    public function testAddressSuccessfullyUpdatedForCustomer(array $postData, array $expectedData): void
    {
        if (isset($expectedData['custom_region_name'])) {
            $expectedData[AddressInterface::REGION_ID] = $this->getRegionIdByName->execute(
                $expectedData['custom_region_name'],
                $expectedData[AddressInterface::COUNTRY_ID]
            );
            unset($expectedData['custom_region_name']);
        }
        $this->performRequestWithData($postData, 1);
        $this->checkRequestPerformedSuccessfully();
        $customer = $this->customerRepository->get('customer@example.com');
        $customerAddresses = $customer->getAddresses();
        $this->assertCount(1, $customerAddresses);
        /** @var AddressInterface $address */
        $address = reset($customerAddresses);
        $createdAddressData = $address->__toArray();
        foreach ($expectedData as $fieldCode => $expectedValue) {
            if (null === $expectedValue) {
                $this->assertArrayNotHasKey($fieldCode, $createdAddressData);
                continue;
            }
            $this->assertArrayHasKey($fieldCode, $createdAddressData, "Field $fieldCode wasn't found.");
            $this->assertEquals($expectedValue, $createdAddressData[$fieldCode]);
        }
    }

    /**
     * Data provider which contain proper POST data for update customer address.
     *
     * @return array
     */
    public function postDataForSuccessUpdateAddressDataProvider(): array
    {
        return [
            'required_fields_valid_data' => [
                self::STATIC_POST_ADDRESS_DATA,
                [
                    AddressInterface::TELEPHONE => 9548642,
                    AddressInterface::COUNTRY_ID => 'US',
                    AddressInterface::POSTCODE => 95556,
                    'custom_region_name' => 'Arkansas',
                    AddressInterface::FIRSTNAME => 'Foma',
                    AddressInterface::LASTNAME => 'Kiniaev',
                    AddressInterface::STREET => ['Yellow str, 228'],
                    AddressInterface::CITY => 'Mukachevo',
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
     * Assert that customer address wasn't updated via controller on frontend
     * when POST data broken.
     *
     * @dataProvider postDataForUpdateAddressWithErrorDataProvider
     *
     * @param array $postData
     * @param array $expectedSessionMessages
     * @return void
     */
    public function testAddressWasntUpdatedForCustomer(array $postData, array $expectedSessionMessages): void
    {
        $this->performRequestWithData($postData, 1);
        $this->checkRequestPerformedWithInputValidationErrors($expectedSessionMessages);
    }

    /**
     * Data provider which contain broken POST data for update customer address with error.
     *
     * @return array
     */
    public function postDataForUpdateAddressWithErrorDataProvider(): array
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
     * @param int $processAddressId
     * @return void
     */
    private function performRequestWithData(array $postData, int $processAddressId): void
    {
        $postData[AddressInterface::ID] = $processAddressId;
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
                $this->containsEqual($this->escaper->escapeHtml((string)__($expectedMessage))),
                MessageInterface::TYPE_ERROR
            );
        }
    }
}
