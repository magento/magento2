<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service\V1;

use Magento\Exception\InputException;
use Magento\Exception\NoSuchEntityException;
use Magento\Customer\Service\V1;

/**
 * Integration test for service layer \Magento\Customer\Service\V1\CustomerAddressService
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CustomerAddressServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerAddressServiceInterface */
    private $_service;

    /** @var \Magento\ObjectManager */
    private $_objectManager;

    /** @var \Magento\Customer\Service\V1\Dto\Address[] */
    private $_expectedAddresses;

    /** @var \Magento\Customer\Service\V1\Dto\AddressBuilder */
    private $_addressBuilder;

    /** @var \Magento\Customer\Service\V1\Dto\CustomerBuilder */
    private $_customerBuilder;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_service = $this->_objectManager->create('Magento\Customer\Service\V1\CustomerAddressServiceInterface');

        $this->_addressBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Dto\AddressBuilder');
        $this->_customerBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Dto\CustomerBuilder');

        $this->_addressBuilder->setId(1)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setDefaultBilling(true)
            ->setDefaultShipping(true)
            ->setPostcode('75477')
            ->setRegion(
                new V1\Dto\Region([
                    'region_code' => 'AL',
                    'region'      => 'Alabama',
                    'region_id'   => 1
                ])
            )
            ->setStreet(['Green str, 67'])
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address = $this->_addressBuilder->create();

        /* XXX: would it be better to have a clear method for this? */
        $this->_addressBuilder->setId(2)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setDefaultBilling(false)
            ->setDefaultShipping(false)
            ->setPostcode('47676')
            ->setRegion(
                new V1\Dto\Region([
                    'region_code' => 'AL',
                    'region'      => 'Alabama',
                    'region_id'   => 1
                ])
            )
            ->setStreet(['Black str, 48'])
            ->setCity('CityX')
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address2 = $this->_addressBuilder->create();

        $this->_expectedAddresses = [$address, $address2];
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testSaveAddressChanges()
    {
        $customerId = 1;
        $address = $this->_service->getAddressById(2);
        $proposedAddressBuilder = $this->_addressBuilder->populate($address);
        $proposedAddressBuilder->setTelephone('555' . $address->getTelephone());
        $proposedAddress = $proposedAddressBuilder->create();

        $this->_service->saveAddresses($customerId, [$proposedAddress]);

        $addresses = $this->_service->getAddresses($customerId);
        $this->assertEquals(2, count($addresses));
        $this->assertNotEquals($this->_expectedAddresses[1], $addresses[1]);
        $this->_assertAddressAndRegionArrayEquals($proposedAddress->__toArray(), $addresses[1]->__toArray());
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testSaveAddressesIdSetButNotAlreadyExisting()
    {
        $proposedAddressBuilder = $this->_createSecondAddressBuilder()
            ->setFirstname('Jane')
            ->setId(4200);
        $proposedAddress = $proposedAddressBuilder->create();

        $customerId = 1;
        $this->_service->saveAddresses($customerId, [$proposedAddress]);
        $addresses = $this->_service->getAddresses($customerId);
        $this->assertEquals($this->_expectedAddresses[0], $addresses[0]);
        $this->assertEquals($this->_expectedAddresses[1], $addresses[1]);

        $expectedThirdAddressBuilder = $this->_addressBuilder->populate($proposedAddress);
        // set id
        $expectedThirdAddressBuilder->setId($addresses[2]->getId());
        $expectedThirdAddress = $expectedThirdAddressBuilder->create();
        $this->_assertAddressAndRegionArrayEquals($expectedThirdAddress->__toArray(), $addresses[2]->__toArray());
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddresses()
    {
        $customerId = 1;
        $addresses = $this->_service->getAddresses($customerId);
        $this->assertEquals(2, count($this->_expectedAddresses));
        $this->assertEquals(2, count($addresses));
        $this->_assertAddressAndRegionArrayEquals(
            $this->_expectedAddresses[0]->__toArray(),
            $addresses[0]->__toArray()
        );
        $this->_assertAddressAndRegionArrayEquals(
            $this->_expectedAddresses[1]->__toArray(),
            $addresses[1]->__toArray()
        );
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testGetDefaultBillingAddress()
    {
        $customerId = 1;
        $address = $this->_service->getDefaultBillingAddress($customerId);
        $this->assertEquals($this->_expectedAddresses[0], $address);
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddressById()
    {
        $addressId = 2;
        $addresses = $this->_service->getAddressById($addressId);
        $this->assertEquals($this->_expectedAddresses[1], $addresses);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetAddressByIdBadAddrId()
    {
        // Should throw the address not found exception
        try {
            $this->_service->getAddressById(12345);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $exception) {
            $this->assertSame($exception->getCode(), \Magento\Exception\NoSuchEntityException::NO_SUCH_ENTITY);
            $this->assertSame(
                $exception->getParams(),
                [
                    'addressId' => 12345
                ]
            );
        }
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testSaveNewAddress()
    {
        $proposedAddressBuilder = $this->_createSecondAddressBuilder();
        $proposedAddress = $proposedAddressBuilder->create();
        $customerId = 1;

        $this->_service->saveAddresses($customerId, [$proposedAddress]);
        $addresses = $this->_service->getAddresses($customerId);
        $this->assertEquals($this->_expectedAddresses[0], $addresses[0]);
        $expectedNewAddressBuilder = $this->_addressBuilder->populate($this->_expectedAddresses[1]);
        $expectedNewAddressBuilder
            ->setId($addresses[1]->getId());
        $expectedNewAddress = $expectedNewAddressBuilder->create();
        $this->assertEquals($expectedNewAddress->__toArray(), $addresses[1]->__toArray());
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testSaveNewAddressWithAttributes()
    {
        $this->_addressBuilder->populateWithArray(
            array_merge(
                $this->_expectedAddresses[1]->__toArray(),
                [
                    'firstname' => 'Jane',
                    'id'        => 4200,
                    'weird'     => 'something_strange_with_hair'
                ]
            )
        )->setId(null);
        $proposedAddress = $this->_addressBuilder->create();

        $customerId = 1;
        $this->_service->saveAddresses($customerId, [$proposedAddress]);

        $addresses = $this->_service->getAddresses($customerId);
        $this->assertNotEquals($proposedAddress->getAttributes(), $addresses[1]->getAttributes());
        $this->assertArrayNotHasKey(
            'weird',
            $proposedAddress->getAttributes(),
            'Only valid attributes should be available.'
        );
        $this->assertArrayNotHasKey(
            'weird',
            $addresses[1]->getAttributes(),
            'Only valid attributes should be available.'
        );
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testSaveNewInvalidAddresses()
    {
        $firstAddressBuilder = $this->_addressBuilder->populateWithArray(
            array_merge(
                $this->_expectedAddresses[0]->__toArray(),
                [
                    'firstname' => null
                ]
            )
        )->setId(null);
        $firstAddress = $firstAddressBuilder->create();
        $secondAddressBuilder = $this->_addressBuilder->populateWithArray(
            array_merge(
                $this->_expectedAddresses[0]->__toArray(),
                [
                    'lastname' => null
                ]
            )
        )->setId(null);
        $secondAddress = $secondAddressBuilder->create();
        $customerId = 1;
        try {
            $this->_service->saveAddresses($customerId, [$firstAddress, $secondAddress]);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (InputException $exception) {
            $this->assertSame($exception->getCode(), \Magento\Exception\InputException::INPUT_EXCEPTION);
            $this->assertSame(
                $exception->getParams(),
                [
                    [
                        'index' => 0,
                        'fieldName' => 'firstname',
                        'code'      => 'REQUIRED_FIELD',
                        'value'     => null
                    ],
                    [
                        'index' => 1,
                        'fieldName' => 'lastname',
                        'code'      => 'REQUIRED_FIELD',
                        'value'     => null
                    ]
                ]
            );
        }
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testSaveNewAddressDefaults()
    {
        $addressShippingBuilder = $this->_createFirstAddressBuilder();
        $addressShippingBuilder->setDefaultShipping(true)->setDefaultBilling(false);
        $addressShipping = $addressShippingBuilder->create();

        $addressBillingBuilder = $this->_createSecondAddressBuilder();
        $addressBillingBuilder->setDefaultBilling(true)->setDefaultShipping(false);
        $addressBilling = $addressBillingBuilder->create();
        $customerId = 1;
        $this->_service->saveAddresses($customerId, [$addressShipping, $addressBilling]);

        $shipping = $this->_service->getDefaultShippingAddress($customerId);
        /* XXX: cannot reuse addressShippingBuilder; actually all of this code
           is re-using the same addressBuilder which is wrong */
        $addressShipping = $this->_addressBuilder->populate($addressShipping)->setId($shipping->getId())->create();
        $this->_assertAddressAndRegionArrayEquals($addressShipping->__toArray(), $shipping->__toArray());

        $billing = $this->_service->getDefaultBillingAddress($customerId);
        $addressBilling = $this->_addressBuilder->populate($addressBilling)->setId($billing->getId())->create();
        $this->_assertAddressAndRegionArrayEquals($addressBilling->__toArray(), $billing->__toArray());
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testSaveSeveralNewAddressesSameDefaults()
    {
        $addressTwoBuilder = $this->_createSecondAddressBuilder();
        $addressTwo = $addressTwoBuilder->create();
        $addressThreeBuilder = $this->_addressBuilder->populate($addressTwo);
        $addressThreeBuilder->setDefaultBilling(true);
        $addressThree = $addressThreeBuilder->create();

        $addressFourBuilder = $this->_addressBuilder->populate($addressTwo);
        $addressFourBuilder->setDefaultBilling(false)->setDefaultShipping(true);
        $addressFour = $addressFourBuilder->create();

        $addressDefaultBuilder = $this->_addressBuilder->populate($addressTwo);
        $addressDefaultBuilder->setDefaultBilling(true)->setDefaultShipping(true)
            ->setFirstname('Dirty Garry');
        $addressDefault = $addressDefaultBuilder->create();

        $customerId = 1;
        $this->_service->saveAddresses(
            $customerId,
            [$addressTwo, $addressThree, $addressFour, $addressDefault]
        );

        $addresses = $this->_service->getAddresses($customerId);
        $this->assertEquals(5, count($addresses));

        // retrieve defaults
        $addresses = [
            $this->_service->getDefaultBillingAddress($customerId),
            $this->_service->getDefaultShippingAddress($customerId),
        ];
        // Same address is returned twice
        $this->assertEquals($addresses[0], $addresses[1]);
        $this->assertEquals($addressDefault->getFirstname(), $addresses[1]->getFirstname());

        //clone object
        $expectedDefaultBuilder = $this->_addressBuilder->populate($addressDefault);
        // It is the same address retrieved as the one which get saved
        $expectedDefaultBuilder->setId($addresses[1]->getId());
        $expectedDefault = $expectedDefaultBuilder->create();
        $this->_assertAddressAndRegionArrayEquals($expectedDefault->__toArray(), $addresses[1]->__toArray());
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testSaveSeveralNewAddressesDifferentDefaults()
    {
        $addressTwoBuilder = $this->_createSecondAddressBuilder();
        $addressTwo = $addressTwoBuilder->create();

        $addressThreeBuilder = $this->_addressBuilder->populate($addressTwo);
        $addressThreeBuilder->setDefaultBilling(true);
        $addressThree = $addressThreeBuilder->create();

        $defaultShippingBuilder = $this->_addressBuilder->populate($addressTwo);
        $defaultShippingBuilder->setFirstname('Shippy')
            ->setLastname('McShippington')
            ->setDefaultBilling(false)
            ->setDefaultShipping(true);
        $defaultShipping = $defaultShippingBuilder->create();

        $defaultBillingBuilder = $this->_addressBuilder->populate($addressTwo);
        $defaultBillingBuilder
            ->setFirstname('Billy')
            ->setLastname('McBillington')
            ->setDefaultBilling(true)
            ->setDefaultShipping(false);
        $defaultBilling = $defaultBillingBuilder->create();

        $customerId = 1;

        $this->_service->saveAddresses($customerId, [$addressTwo, $addressThree, $defaultShipping, $defaultBilling]);
        $addresses = $this->_service->getAddresses($customerId);

        $this->assertEquals(5, count($addresses));

        $addresses = [
            $this->_service->getDefaultBillingAddress($customerId),
            $this->_service->getDefaultShippingAddress($customerId),
        ];
        $this->assertNotEquals($addresses[0], $addresses[1]);
        $this->assertTrue($addresses[0]->isDefaultBilling());
        $this->assertTrue($addresses[1]->isDefaultShipping());

        $expectedDfltShipBuilder = $this->_addressBuilder->populate($defaultShipping);
        $expectedDfltShipBuilder->setId($addresses[1]->getId());
        $expectedDfltShip = $expectedDfltShipBuilder->create();

        $expectedDfltBillBuilder = $this->_addressBuilder->populate($defaultBilling);
        $expectedDfltBillBuilder->setId($addresses[0]->getId());
        $expectedDfltBill = $expectedDfltBillBuilder->create();

        $this->_assertAddressAndRegionArrayEquals($expectedDfltShip->__toArray(), $addresses[1]->__toArray());
        $this->_assertAddressAndRegionArrayEquals($expectedDfltBill->__toArray(), $addresses[0]->__toArray());
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testSaveAddressesNoAddresses()
    {
        $addressIds = $this->_service->saveAddresses(1, []);
        $this->assertEmpty($addressIds);
        $customerId = 1;
        $addresses = $this->_service->getAddresses($customerId);
        $this->assertEquals($this->_expectedAddresses, $addresses);
    }

    public function testSaveAddressesCustomerIdNotExist()
    {
        $proposedAddress = $this->_createSecondAddressBuilder()->create();
        try {
            $this->_service->saveAddresses(4200, [$proposedAddress]);
            $this->fail('Expected exception not thrown');
        } catch (NoSuchEntityException $nsee) {
            $expectedParams = [
                'customerId' => '4200',
            ];
            $this->assertEquals($expectedParams, $nsee->getParams());
            $this->assertEquals('No such entity with customerId = 4200', $nsee->getMessage());
        }
    }

    public function testSaveAddressesCustomerIdInvalid()
    {
        $proposedAddress = $this->_createSecondAddressBuilder()->create();
        try {
            $this->_service->saveAddresses('this_is_not_a_valid_id', [$proposedAddress]);
            $this->fail('Expected exception not thrown');
        } catch (NoSuchEntityException $nsee) {
            $expectedParams = [
                'customerId' => 'this_is_not_a_valid_id',
            ];
            $this->assertEquals($expectedParams, $nsee->getParams());
            $this->assertEquals('No such entity with customerId = this_is_not_a_valid_id', $nsee->getMessage());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testDeleteAddressFromCustomer()
    {
        $addressId = 1;
        // See that customer already has an address with expected addressId
        $addressDto = $this->_service->getAddressById($addressId);
        $this->assertEquals($addressDto->getId(), $addressId);

        // Delete the address from the customer
        $this->_service->deleteAddress($addressId);

        // See that address is deleted
        try {
            $addressDto = $this->_service->getAddressById($addressId);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $exception) {
            $this->assertSame($exception->getCode(), \Magento\Exception\NoSuchEntityException::NO_SUCH_ENTITY);
            $this->assertSame(
                $exception->getParams(),
                [
                    'addressId' => $addressId
                ]
            );
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testDeleteAddressFromCustomerBadAddrId()
    {
        // Should throw the address not found exception
        try {
            $this->_service->deleteAddress(12345);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $exception) {
            $this->assertSame($exception->getCode(), \Magento\Exception\NoSuchEntityException::NO_SUCH_ENTITY);
            $this->assertSame(
                $exception->getParams(),
                [
                    'addressId' => 12345
                ]
            );
        }
    }

    /**
     * Helper function that returns an Address DTO that matches the data from customer_address fixture
     *
     * @return \Magento\Customer\Service\V1\Dto\AddressBuilder
     */
    private function _createFirstAddressBuilder()
    {
        $addressBuilder = $this->_addressBuilder->populate($this->_expectedAddresses[0]);
        $addressBuilder->setId(null);
        return $addressBuilder;
    }

    /**
     * Helper function that returns an Address DTO that matches the data from customer_two_address fixture
     *
     * @return \Magento\Customer\Service\V1\Dto\AddressBuilder
     */
    private function _createSecondAddressBuilder()
    {
        return $this->_addressBuilder->populate($this->_expectedAddresses[1])
            ->setId(null);
    }

    /**
     * Checks that the arrays are equal, but accounts for the 'region' being an object
     *
     * @param array $expectedArray
     * @param array $actualArray
     */
    protected function _assertAddressAndRegionArrayEquals($expectedArray, $actualArray)
    {
        if (array_key_exists('region', $expectedArray)) {
            /** @var \Magento\Customer\Service\V1\Dto\Region $expectedRegion */
            $expectedRegion = $expectedArray['region'];
            unset($expectedArray['region']);
        }
        if (array_key_exists('region', $actualArray)) {
            /** @var \Magento\Customer\Service\V1\Dto\Region $actualRegion */
            $actualRegion = $actualArray['region'];
            unset($actualArray['region']);
        }

        $this->assertEquals($expectedArray, $actualArray);

        // Either both set or both unset
        $this->assertTrue(!(isset($expectedRegion) xor isset($actualRegion)));
        if (isset($expectedRegion) && isset($actualRegion)) {
            $this->assertTrue(is_array($expectedRegion));
            $this->assertTrue(is_array($actualRegion));
            $this->assertEquals($expectedRegion, $actualRegion);
        }
    }
}
