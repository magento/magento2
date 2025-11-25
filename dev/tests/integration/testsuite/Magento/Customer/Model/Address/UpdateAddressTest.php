<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Address;
use Magento\Framework\Exception\InputException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Assert that address was updated as expected or address update throws expected error.
 *
 * @magentoDbIsolation enabled
 */
class UpdateAddressTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AddressRegistry
     */
    protected $addressRegistry;

    /**
     * @var Address
     */
    protected $addressResource;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var int[]
     */
    protected $processedAddressesIds = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->addressRegistry = $this->objectManager->get(AddressRegistry::class);
        $this->addressResource = $this->objectManager->get(Address::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        foreach ($this->processedAddressesIds as $createdAddressesId) {
            $this->addressRegistry->remove($createdAddressesId);
        }
        parent::tearDown();
    }

    /**
     * Assert that default addresses properly updated for customer.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @dataProvider updateAddressIsDefaultDataProvider
     *
     * @param bool $isShippingDefault
     * @param bool $isBillingDefault
     * @param int|null $expectedShipping
     * @param int|null $expectedBilling
     * @return void
     */
    public function testUpdateAddressIsDefault(
        bool $isShippingDefault,
        bool $isBillingDefault,
        ?int $expectedShipping,
        ?int $expectedBilling
    ): void {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertEquals(1, $customer->getDefaultShipping());
        $this->assertEquals(1, $customer->getDefaultBilling());
        $this->processedAddressesIds[] = 1;
        $address = $this->addressRepository->getById(1);
        $address->setIsDefaultShipping($isShippingDefault);
        $address->setIsDefaultBilling($isBillingDefault);
        $this->addressRepository->save($address);
        $this->customerRegistry->remove(1);
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertEquals($customer->getDefaultShipping(), $expectedShipping);
        $this->assertEquals($customer->getDefaultBilling(), $expectedBilling);
    }

    /**
     * Data provider for update address as default billing or default shipping.
     *
     * @return array
     */
    public static function updateAddressIsDefaultDataProvider(): array
    {
        return [
            'update_shipping_address_default' => [true, false, 1, null],
            'update_billing_address_default' => [false, true, null, 1],
        ];
    }

    /**
     * Assert that address updated successfully.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @dataProvider updateAddressesDataProvider
     *
     * @param array $updateData
     * @param array $expectedData
     * @return void
     */
    public function testUpdateAddress(array $updateData, array $expectedData): void
    {
        $this->processedAddressesIds[] = 1;
        $address = $this->addressRepository->getById(1);
        foreach ($updateData as $setFieldName => $setValue) {
            $address->setData($setFieldName, $setValue);
        }
        $updatedAddressData = $this->addressRepository->save($address)->__toArray();
        foreach ($expectedData as $getFieldName => $getValue) {
            $this->assertTrue(isset($updatedAddressData[$getFieldName]), "Field $getFieldName wasn't found.");
            $this->assertEquals($getValue, $updatedAddressData[$getFieldName]);
        }
    }

    /**
     * Data provider for update address with proper data.
     *
     * @return array
     */
    public static function updateAddressesDataProvider(): array
    {
        return [
            'required_field_telephone' => [
                [AddressInterface::TELEPHONE => 251512979595],
                [AddressInterface::TELEPHONE => 251512979595],
            ],
            'required_field_postcode' => [
                [AddressInterface::POSTCODE => 55425],
                [AddressInterface::POSTCODE => 55425],
            ],
            'required_field_empty_postcode_for_uk' => [
                [AddressInterface::COUNTRY_ID => 'GB', AddressInterface::POSTCODE => ''],
                [AddressInterface::COUNTRY_ID => 'GB', AddressInterface::POSTCODE => null],
            ],
            'required_field_empty_region_id_for_ua' => [
                [AddressInterface::COUNTRY_ID => 'UA', AddressInterface::REGION_ID => ''],
                [
                    AddressInterface::COUNTRY_ID => 'UA',
                    AddressInterface::REGION_ID => 0,
                ],
            ],
            'required_field_firstname' => [
                [AddressInterface::FIRSTNAME => 'Test firstname'],
                [AddressInterface::FIRSTNAME => 'Test firstname'],
            ],
            'required_field_lastname' => [
                [AddressInterface::LASTNAME => 'Test lastname'],
                [AddressInterface::LASTNAME => 'Test lastname'],
            ],
            'required_field_street_as_array' => [
                [AddressInterface::STREET => ['', 'Test str, 55']],
                [AddressInterface::STREET => ['Test str, 55']],
            ],
            'required_field_city' => [
                [AddressInterface::CITY => 'Test city'],
                [AddressInterface::CITY => 'Test city'],
            ],
            'field_name_prefix' => [
                [AddressInterface::PREFIX => 'My prefix'],
                [AddressInterface::PREFIX => 'My prefix'],
            ],
            'field_middle_name_initial' => [
                [AddressInterface::MIDDLENAME => 'My middle name'],
                [AddressInterface::MIDDLENAME => 'My middle name'],
            ],
            'field_name_suffix' => [
                [AddressInterface::SUFFIX => 'My suffix'],
                [AddressInterface::SUFFIX => 'My suffix'],
            ],
            'field_company_name' => [
                [AddressInterface::COMPANY => 'My company'],
                [AddressInterface::COMPANY => 'My company'],
            ],
            'field_vat_number' => [
                [AddressInterface::VAT_ID => 'My VAT number'],
                [AddressInterface::VAT_ID => 'My VAT number'],
            ],
        ];
    }

    /**
     * Assert that error message has thrown during process address update.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @dataProvider updateWrongAddressesDataProvider
     *
     * @param array $updateData
     * @param \Exception $expectException
     * @return void
     */
    public function testExceptionThrownDuringUpdateAddress(array $updateData, \Exception $expectException): void
    {
        $this->processedAddressesIds[] = 1;
        $address = $this->addressRepository->getById(1);
        foreach ($updateData as $setFieldName => $setValue) {
            $address->setData($setFieldName, $setValue);
        }
        $this->expectExceptionObject($expectException);
        $this->addressRepository->save($address);
    }

    /**
     * Data provider for update address with proper data or with error.
     *
     * @return array
     */
    public static function updateWrongAddressesDataProvider(): array
    {
        return [
            'required_field_empty_telephone' => [
                [AddressInterface::TELEPHONE => ''],
                InputException::requiredField('telephone'),
            ],
            'required_field_empty_postcode_for_us' => [
                [AddressInterface::POSTCODE => ''],
                InputException::requiredField('postcode'),
            ],
// TODO: Uncomment this variation after fix issue https://jira.corp.magento.com/browse/MC-31031
//            'required_field_empty_region_id_for_us' => [
//                [AddressInterface::REGION_ID => ''],
//                InputException::requiredField('regionId'),
//            ],
            'required_field_empty_firstname' => [
                [AddressInterface::FIRSTNAME => ''],
                InputException::requiredField('firstname'),
            ],
            'required_field_empty_lastname' => [
                [AddressInterface::LASTNAME => ''],
                InputException::requiredField('lastname'),
            ],
            'required_field_empty_street_as_array' => [
                [AddressInterface::STREET => []],
                InputException::requiredField('street'),
            ],
            'required_field_empty_city' => [
                [AddressInterface::CITY => ''],
                InputException::requiredField('city'),
            ],
// TODO: Uncomment this variation after fix issue https://jira.corp.magento.com/browse/MC-31031
//            'field_invalid_vat_number' => [
//                [AddressInterface::VAT_ID => '/>.<*'],
//                null// It need to create some error but currently magento doesn't has validation for this field.,
//            ],
        ];
    }
}
