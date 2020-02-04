<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
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
    private $addressRegistry;

    /**
     * @var Address
     */
    private $addressResource;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var int[]
     */
    private $processedAddressesIds = [];

    /**
     * @inheritdoc
     */
    protected function setUp()
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
    protected function tearDown()
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
    public function updateAddressIsDefaultDataProvider(): array
    {
        return [
            'update_shipping_address_default' => [true, false, 1, null],
            'update_billing_address_default' => [false, true, null, 1],
        ];
    }

    /**
     * Assert that address updated successfully or proper error message has thrown.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @dataProvider updateAddressesDataProvider
     *
     * @param array $updateData
     * @param array $expectedData
     * @param Exception|null $expectException
     * @return void
     */
    public function testUpdateAddress(
        array $updateData,
        array $expectedData,
        ?Exception $expectException = null
    ): void {
        $this->processedAddressesIds[] = 1;
        $address = $this->addressRepository->getById(1);
        foreach ($updateData as $setMethod => $setValue) {
            $address->$setMethod($setValue);
        }
        if (null !== $expectException) {
            $this->expectExceptionObject($expectException);
        }
        $updatedAddress = $this->addressRepository->save($address);
        foreach ($expectedData as $getMethod => $getValue) {
            $this->assertEquals($getValue, $updatedAddress->$getMethod());
        }
    }

    /**
     * Data provider for update address with proper data or with error.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function updateAddressesDataProvider(): array
    {
        return [
            'required_field_telephone' => [
                ['setTelephone' => 251512979595],
                ['getTelephone' => 251512979595],
                null,
            ],
            'required_field_empty_telephone' => [
                ['setTelephone' => ''],
                [],
                $this->createInputException('"telephone" is required. Enter and try again.'),
            ],
            'required_field_postcode' => [
                ['setPostcode' => 55425],
                ['getPostcode' => 55425],
                null,
            ],
            'required_field_empty_postcode_for_us' => [
                ['setPostcode' => ''],
                [],
                $this->createInputException('"postcode" is required. Enter and try again.'),
            ],
            'required_field_empty_postcode_for_uk' => [
                ['setCountryId' => 'GB', 'setPostcode' => ''],
                ['getCountryId' => 'GB', 'getPostcode' => null],
                null,
            ],
// TODO: Uncomment this variation after fix issue https://jira.corp.magento.com/browse/MC-31031
//            'required_field_empty_region_id_for_us' => [
//                ['setRegionId' => ''],
//                [],
//                $this->createInputException('"regionId" is required. Enter and try again.'),
//            ],
            'required_field_empty_region_id_for_ua' => [
                ['setCountryId' => 'UA', 'setRegionId' => ''],
                ['getCountryId' => 'UA', 'getRegionId' => null],
                null,
            ],
            'required_field_firstname' => [
                ['setFirstname' => 'Test firstname'],
                ['getFirstname' => 'Test firstname'],
                null,
            ],
            'required_field_empty_firstname' => [
                ['setFirstname' => ''],
                [],
                $this->createInputException('"firstname" is required. Enter and try again.'),
            ],
            'required_field_lastname' => [
                ['setLastname' => 'Test lastname'],
                ['getLastname' => 'Test lastname'],
                null,
            ],
            'required_field_empty_lastname' => [
                ['setLastname' => ''],
                [],
                $this->createInputException('"lastname" is required. Enter and try again.'),
            ],
            'required_field_street_as_array' => [
                ['setStreet' => ['', 'Test str, 55']],
                ['getStreet' => ['Test str, 55']],
                null
            ],
            'required_field_empty_street_as_array' => [
                ['setStreet' => []],
                [],
                $this->createInputException('"street" is required. Enter and try again.'),
            ],
            'required_field_city' => [
                ['setCity' => 'Test city'],
                ['getCity' => 'Test city'],
                null,
            ],
            'required_field_empty_city' => [
                ['setCity' => ''],
                [],
                $this->createInputException('"city" is required. Enter and try again.'),
            ],
            'field_name_prefix' => [
                ['setPrefix' => 'My prefix'],
                ['getPrefix' => 'My prefix'],
                null,
            ],
            'field_middle_name_initial' => [
                ['setMiddlename' => 'My middle name'],
                ['getMiddlename' => 'My middle name'],
                null,
            ],
            'field_name_suffix' => [
                ['setSuffix' => 'My suffix'],
                ['getSuffix' => 'My suffix'],
                null,
            ],
            'field_company_name' => [
                ['setCompany' => 'My company'],
                ['getCompany' => 'My company'],
                null,
            ],
            'field_vat_number' => [
                ['setVatId' => 'My VAT number'],
                ['getVatId' => 'My VAT number'],
                null,
            ],
// TODO: Uncomment this variation after fix issue https://jira.corp.magento.com/browse/MC-31031
//            'field_invalid_vat_number' => [
//                ['setVatId' => '/>.<*'],
//                [],
//                null// It need to create some error but currently magento doesn't has validation for this field.,
//            ],
        ];
    }

    /**
     * Create InputException with provided error text.
     *
     * @param string $string
     * @return InputException
     */
    private function createInputException(string $string): InputException
    {
        $inputException = new InputException();
        $inputException->addError(__($string));

        return $inputException;
    }
}
