<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;

/**
 * Class to test Sales Quote address model functionality
 *
 * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
 * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
 */
class AddressTest extends TestCase
{
    /** @var Quote $quote */
    protected $_quote;

    /** @var CustomerInterface $customer */
    protected $_customer;

    /** @var Address */
    protected $_address;

    /**@var CustomerRepositoryInterface $customerRepository */
    protected $customerRepository;

    /** @var AddressRepositoryInterface $addressRepository */
    protected $addressRepository;

    /** @var DataObjectProcessor */
    protected $dataProcessor;

    /**
     * phpcs:ignoreFile
     */
    public static function setUpBeforeClass(): void
    {
        $db = Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        parent::setUpBeforeClass();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_quote = Bootstrap::getObjectManager()->create(
            Quote::class
        );
        $this->_quote->load('test01', 'reserved_order_id');
        $this->_quote->setIsMultiShipping('0');

        $this->customerRepository = Bootstrap::getObjectManager()->create(
            CustomerRepositoryInterface::class
        );
        $this->_customer = $this->customerRepository->getById(1);

        /** @var \Magento\Sales\Model\Order\Address $address */
        $this->_address = Bootstrap::getObjectManager()->create(
            Address::class
        );
        $this->_address->setId(1);
        $this->_address->load($this->_address->getId());
        $this->_address->setQuote($this->_quote);

        $this->addressRepository = Bootstrap::getObjectManager()->create(
            AddressRepositoryInterface::class
        );

        $this->dataProcessor = Bootstrap::getObjectManager()->create(
            DataObjectProcessor::class
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        /** @var CustomerRegistry $customerRegistry */
        $customerRegistry = Bootstrap::getObjectManager()
            ->get(CustomerRegistry::class);
        //Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    /**
     * same_as_billing must be equal 0 if billing address is being saved
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     */
    public function testSameAsBillingForBillingAddress($unsetId)
    {
        $this->_quote->setCustomer($this->_customer);
        $address = $this->_quote->getBillingAddress();
        if ($unsetId) {
            $address->setId(null);
        }
        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()
            ->create(AddressRepositoryInterface::class);
        $customerAddressData = $addressRepository->getById($this->_customer->getDefaultBilling());
        $address->setSameAsBilling(0)->setCustomerAddressData($customerAddressData)->save();
        $this->assertEquals(0, $this->_quote->getBillingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if customer is guest
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     */
    public function testSameAsBillingWhenCustomerIsGuest($unsetId)
    {
        $shippingAddress = $this->_quote->getShippingAddress();
        if ($unsetId) {
            $shippingAddress->setId(null);
        }
        $shippingAddress->setSameAsBilling(0);
        $shippingAddress->save();
        $this->assertEquals((int)$unsetId, $shippingAddress->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if quote address has no customer address
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     */
    public function testSameAsBillingWhenQuoteAddressHasNoCustomerAddress($unsetId)
    {
        $this->_quote->setCustomer($this->_customer);
        $shippingAddress = $this->_quote->getShippingAddress();
        if ($unsetId) {
            $shippingAddress->setId(null);
        }
        $shippingAddress->setSameAsBilling(0)
            ->setCustomerAddressData(null)
            ->save();
        $this->assertEquals((int)$unsetId, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 1 if customer registered and he has no default shipping address
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     * @magentoDbIsolation enabled
     */
    public function testSameAsBillingWhenCustomerHasNoDefaultShippingAddress($unsetId)
    {
        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()
            ->create(AddressRepositoryInterface::class);
        $this->_customer->setDefaultShipping(1)
            ->setAddresses(
                [
                    $addressRepository->getById($this->_address->getId()),
                ]
            );

        $this->_customer = $this->customerRepository->save($this->_customer);
        // we should save the customer data in order to be able to use it
        $this->_quote->setCustomer($this->_customer);
        $this->_setCustomerAddressAndSave($unsetId);
        $sameAsBilling = $this->_quote->getShippingAddress()->getSameAsBilling();
        $this->assertEquals((int)$unsetId, $sameAsBilling);
    }

    /**
     * same_as_billing must be equal 1 if customer has the same billing and shipping address
     *
     * @param bool $unsetId
     * @dataProvider unsetAddressIdDataProvider
     * @magentoDbIsolation enabled
     */
    public function testSameAsBillingWhenCustomerHasBillingSameShipping($unsetId)
    {
        $this->_quote->setCustomer($this->_customer);
        $this->_setCustomerAddressAndSave($unsetId);
        $this->assertEquals((int)$unsetId, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * same_as_billing must be equal 0 if customer has default shipping address that differs from default billing
     *
     * @magentoDbIsolation enabled
     */
    public function testSameAsBillingWhenCustomerHasDefaultShippingAddress()
    {
        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()
            ->create(AddressRepositoryInterface::class);
        $this->_customer->setDefaultShipping(1)
            ->setAddresses([$addressRepository->getById($this->_address->getId())]);
        $this->_customer = $this->customerRepository->save($this->_customer);
        // we should save the customer data in order to be able to use it
        $this->_quote->setCustomer($this->_customer);

        $sameAsBilling = $this->_quote->getShippingAddress()->getSameAsBilling();
        $this->assertEquals(1, $sameAsBilling);
    }

    /**
     * Assign customer address to quote address and save quote address
     *
     * @param bool $unsetId
     */
    protected function _setCustomerAddressAndSave($unsetId)
    {
        $shippingAddress = $this->_quote->getShippingAddress();
        if ($unsetId) {
            $shippingAddress->setId(null);
        }
        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()
            ->create(AddressRepositoryInterface::class);
        $shippingAddress->setSameAsBilling(0)
            ->setCustomerAddressData($addressRepository->getById($this->_customer->getDefaultBilling()))
            ->save();
    }

    /**
     * @return array
     */
    public static function unsetAddressIdDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * Test to get same as billing flag after change quote customer
     */
    public function testSameAsBillingAfterCustomerWesChanged()
    {
        $shippingAddressId = 2;
        $this->_quote->setCustomer($this->_customer);
        /** Make different default shipping and default billing addresses */
        $this->_customer->setDefaultShipping($shippingAddressId);
        $this->_quote->getShippingAddress()->setCustomerAddressId($shippingAddressId);
        /** Emulate to change customer */
        $this->_quote->setOrigData('customer_id', null);
        $shippingAddress = $this->_quote->getShippingAddress();
        $shippingAddress->beforeSave();
        $this->assertSame(0, $this->_quote->getShippingAddress()->getSameAsBilling());
    }

    /**
     * Import customer address to quote address
     */
    public function testImportCustomerAddressDataWithCustomer()
    {
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';
        $city = 'TestCity';
        $street = 'Street1';

        /** @var AddressInterfaceFactory $addressFactory */
        $addressFactory = Bootstrap::getObjectManager()->create(
            AddressInterfaceFactory::class
        );
        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()->create(
            AddressRepositoryInterface::class
        );
        $addressData = $addressFactory->create()
            ->setCustomerId($customerIdFromFixture)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setTelephone('123456')
            ->setPostcode('12345')
            ->setCountryId('US')
            ->setCity($city)
            ->setStreet([$street]);
        $addressData = $addressRepository->save($addressData);
        $this->_address->setQuote($this->_quote);
        $this->_address->importCustomerAddressData($addressData);

        $this->assertEquals($customerEmailFromFixture, $this->_address->getEmail(), 'Email was imported incorrectly.');
        $this->assertEquals($city, $this->_address->getCity(), 'City was imported incorrectly.');
        $this->assertEquals($street, $this->_address->getStreetFull(), 'Imported street is invalid.');
    }

    /**
     * Export customer address from quote address
     */
    public function testExportCustomerAddressData()
    {
        $street = ['Street1'];
        $company = 'TestCompany';

        $this->_address->setStreet($street);
        $this->_address->setCompany($company);

        $customerAddress = $this->_address->exportCustomerAddress();

        $this->assertEquals($street, $customerAddress->getStreet(), 'Street was exported incorrectly.');
        $this->assertEquals($company, $customerAddress->getCompany(), 'Company was exported incorrectly.');
    }

    /**
     * Test to Set the required fields
     */
    public function testPopulateBeforeSaveData()
    {
        /** Preconditions */
        $customerId = 1;
        $customerAddressId = 1;

        $this->_address->setQuote($this->_quote);
        $this->assertNotEquals(
            $customerId,
            $this->_address->getCustomerId(),
            "Precondition failed: Customer ID was not set."
        );
        $this->assertNotEquals(1, $this->_address->getQuoteId(), "Precondition failed: Quote ID was not set.");
        $this->assertNotEquals(
            $customerAddressId,
            $this->_address->getCustomerAddressId(),
            "Precondition failed: Customer address ID was not set."
        );

        /** @var AddressInterfaceFactory $addressFactory */
        $addressFactory = Bootstrap::getObjectManager()->create(
            AddressInterfaceFactory::class
        );
        $customerAddressData = $addressFactory->create()->setId($customerAddressId);
        $this->_address->setCustomerAddressData($customerAddressData);
        $this->_address->save();

        $this->assertEquals($customerId, $this->_address->getCustomerId());
        $this->assertEquals($this->_quote->getId(), $this->_address->getQuoteId());
        $this->assertEquals($customerAddressId, $this->_address->getCustomerAddressId());
    }

    /**
     * Test to retrieve applied taxes
     *
     * @param $taxes
     * @param $expected
     * @covers \Magento\Quote\Model\Quote\Address::setAppliedTaxes()
     * @covers \Magento\Quote\Model\Quote\Address::getAppliedTaxes()
     * @dataProvider appliedTaxesDataProvider
     */
    public function testAppliedTaxes($taxes, $expected)
    {
        $this->_address->setAppliedTaxes($taxes);
        $this->assertSame($expected, $this->_address->getAppliedTaxes());
    }

    /**
     * Retrieve applied taxes data provider
     *
     * @return array
     */
    public static function appliedTaxesDataProvider()
    {
        return [
            ['test', 'test'],
            [[123, true], [123, true]]
        ];
    }

    /**
     * Test to sate shipping address without region
     */
    public function testSaveShippingAddressWithEmptyRegionId()
    {
        $customerAddress = $this->addressRepository->getById(1);
        $customerAddress->setRegionId(0);

        $address = $this->dataProcessor->buildOutputDataArray(
            $customerAddress,
            AddressInterface::class
        );

        $shippingAddress = $this->_quote->getShippingAddress();
        $shippingAddress->addData($address);

        $shippingAddress->save();

        $this->assertEquals(0, $shippingAddress->getRegionId());
        $this->assertEquals(0, $shippingAddress->getRegion());
    }
}
