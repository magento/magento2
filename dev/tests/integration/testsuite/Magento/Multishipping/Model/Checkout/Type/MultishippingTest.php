<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Model\Checkout\Type;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea frontend
 */
class MultishippingTest extends \PHPUnit_Framework_TestCase
{
    const ADDRESS_TYPE_SHIPPING = 'shipping';

    const ADDRESS_TYPE_BILLING = 'billing';

    /** @var \Magento\Multishipping\Model\Checkout\Type\Multishipping */
    protected $_multishippingCheckout;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    protected function setUp()
    {
        $this->_multishippingCheckout = Bootstrap::getObjectManager()->create(
            'Magento\Multishipping\Model\Checkout\Type\Multishipping'
        );
        $this->addressRepository = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\AddressRepositoryInterface'
        );
        parent::setUp();
    }

    /**
     * Test case when default billing and shipping addresses are set and they are different.
     *
     * @param string $addressType
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     * @dataProvider getCustomerDefaultAddressDataProvider
     */
    public function testGetCustomerDefaultAddress($addressType)
    {
        /**
         * Preconditions:
         * - second address is default address of {$addressType},
         * - current customer is set to customer session
         */
        $fixtureCustomerId = 1;
        $secondFixtureAddressId = 2;
        $secondFixtureAddressStreet = ['Black str, 48'];
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer')->load($fixtureCustomerId);
        if ($addressType == self::ADDRESS_TYPE_SHIPPING) {
            $customer->setDefaultShipping($secondFixtureAddressId)->save();
        } else {
            // billing
            $customer->setDefaultBilling($secondFixtureAddressId)->save();
        }
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $customerSession->setCustomer($customer);

        /** Execute SUT */
        if ($addressType == self::ADDRESS_TYPE_SHIPPING) {
            $addressId = $this->_multishippingCheckout->getCustomerDefaultShippingAddress();
        } else {
            // billing
            $addressId = $this->_multishippingCheckout->getCustomerDefaultBillingAddress();
        }
        $address = $this->addressRepository->getById($addressId);

        $this->assertInstanceOf(
            '\Magento\Customer\Api\Data\AddressInterface',
            $address,
            "Address was not loaded."
        );
        $this->assertEquals($secondFixtureAddressId, $address->getId(), "Invalid address loaded.");
        $this->assertEquals(
            $secondFixtureAddressStreet,
            $address->getStreet(),
            "Street in default {$addressType} address is invalid."
        );

        /** Ensure that results are cached properly by changing default address and invoking SUT once again */
        $firstFixtureAddressId = 1;
        if ($addressType == self::ADDRESS_TYPE_SHIPPING) {
            $customer->setDefaultShipping($firstFixtureAddressId)->save();
            $addressId = $this->_multishippingCheckout->getCustomerDefaultShippingAddress();
        } else {
            // billing
            $customer->setDefaultBilling($firstFixtureAddressId)->save();
            $addressId = $this->_multishippingCheckout->getCustomerDefaultBillingAddress();
        }
        $address = $this->addressRepository->getById($addressId);

        $this->assertEquals($secondFixtureAddressId, $address->getId(), "Method results are not cached properly.");
    }

    /**
     * Test case when customer has addresses, but default {$addressType} address is not set.
     *
     * @param string $addressType
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     * @dataProvider getCustomerDefaultAddressDataProvider
     */
    public function testGetCustomerDefaultAddressDefaultAddressNotSet($addressType)
    {
        /**
         * Preconditions:
         * - customer has addresses, but default address of {$addressType} is not set
         * - current customer is set to customer session
         */
        $fixtureCustomerId = 1;
        $firstFixtureAddressId = 1;
        $firstFixtureAddressStreet = ['Green str, 67'];
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer')->load($fixtureCustomerId);
        if ($addressType == self::ADDRESS_TYPE_SHIPPING) {
            $customer->setDefaultShipping(null)->save();
        } else {
            // billing
            $customer->setDefaultBilling(null)->save();
        }
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $customerSession->setCustomer($customer);

        /** Execute SUT */
        if ($addressType == self::ADDRESS_TYPE_SHIPPING) {
            $addressId = $this->_multishippingCheckout->getCustomerDefaultShippingAddress();
        } else {
            // billing
            $addressId = $this->_multishippingCheckout->getCustomerDefaultBillingAddress();
        }
        $address = $this->addressRepository->getById($addressId);

        $this->assertInstanceOf('\Magento\Customer\Api\Data\AddressInterface', $address, "Address was not loaded.");
        $this->assertEquals($firstFixtureAddressId, $address->getId(), "Invalid address loaded.");
        $this->assertEquals(
            $firstFixtureAddressStreet,
            $address->getStreet(),
            "Street in default {$addressType} address is invalid."
        );
    }

    /**
     * Test case when customer has no addresses.
     *
     * @param string $addressType
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     * @dataProvider getCustomerDefaultAddressDataProvider
     */
    public function testGetCustomerDefaultAddressCustomerWithoutAddresses($addressType)
    {
        /**
         * Preconditions:
         * - customer has no addresses
         * - current customer is set to customer session
         */
        $fixtureCustomerId = 1;
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer')->load($fixtureCustomerId);
        $customer->setDefaultShipping(null)->setDefaultBilling(null)->save();
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $customerSession->setCustomer($customer);

        /** Execute SUT */
        if ($addressType == self::ADDRESS_TYPE_SHIPPING) {
            $address = $this->_multishippingCheckout->getCustomerDefaultShippingAddress();
        } else {
            // billing
            $address = $this->_multishippingCheckout->getCustomerDefaultBillingAddress();
        }
        $this->assertNull($address, "When customer has no addresses, null is expected.");
    }

    public function getCustomerDefaultAddressDataProvider()
    {
        return [
            self::ADDRESS_TYPE_SHIPPING => [self::ADDRESS_TYPE_SHIPPING],
            self::ADDRESS_TYPE_BILLING => [self::ADDRESS_TYPE_BILLING]
        ];
    }
}
