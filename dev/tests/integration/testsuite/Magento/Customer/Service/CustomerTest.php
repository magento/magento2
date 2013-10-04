<?php
/**
 * Integration test for service layer \Magento\Customer\Service\Customer
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Service\Customer
     */
    protected $_model;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager = null;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_createdCustomer;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory = null;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerFactory = $this->_objectManager->get('Magento\Customer\Model\CustomerFactory');
        $this->_model = $this->_objectManager->create('Magento\Customer\Service\Customer');
    }

    protected function tearDown()
    {
        $previousStoreId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Core\Model\StoreManagerInterface')->getStore();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
            ->setCurrentStore(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->get('Magento\Core\Model\StoreManagerInterface')
                    ->getStore(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
            );
        if ($this->_createdCustomer && $this->_createdCustomer->getId() > 0) {
            $this->_createdCustomer->getAddressesCollection()->delete();
            $this->_createdCustomer->delete();
        }
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
            ->setCurrentStore($previousStoreId);

        $this->_model = null;
    }

    /**
     * @param array $customerData
     * @dataProvider createDataProvider
     */
    public function testCreate($customerData)
    {
        $this->_createdCustomer = $this->_model->create($customerData);
        $this->assertInstanceOf('Magento\Customer\Model\Customer', $this->_createdCustomer);
        $this->assertNotEmpty($this->_createdCustomer->getId());

        $loadedCustomer = $this->_customerFactory->create()
            ->load($this->_createdCustomer->getId());
        if (array_key_exists('sendemail', $customerData)) {
            unset($customerData['sendemail']);
        }
        $expectedData = $customerData;
        $actualData = $loadedCustomer->toArray(array_keys($customerData));
        if (isset($expectedData['password'])) {
            // TODO Add assertions for password if needed
            unset($expectedData['password'], $actualData['password']);
        }
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return array(
            'Valid data' => array(array(
                'website_id' => 1,
                'sendemail' => true,
                'group_id' => 1,
                'disable_auto_group_change' => 0,
                'prefix' => 'Prefix',
                'firstname' => 'Service',
                'middlename' => 'Middlename',
                'lastname' => 'CreateValid',
                'suffix' => 'Suffix',
                'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
                'dob' => date('Y-m-d H:i:s'),
                'taxvat' => null,
                'gender' => 1,
                'password' => '123123q',
                'default_billing' => null,
                'default_shipping' => null,
                'store_id' => \Magento\Core\Model\AppInterface::ADMIN_STORE_ID
            )),
            'Mandatory data' => array(array(
                'firstname' => 'SomeName',
                'lastname' => 'CreateMandatory',
                'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
            )),
        );
    }

    /**
     * @param array $customerData
     * @param string $exceptionName
     * @param string $exceptionText
     * @dataProvider createExceptionsDataProvider
     */
    public function testCreateExceptions($customerData, $exceptionName, $exceptionText = '')
    {
        $this->setExpectedException($exceptionName, $exceptionText);
        $this->_createdCustomer = $this->_model->create($customerData);
    }

    /**
     * @return array
     */
    public function createExceptionsDataProvider()
    {
        return array(
            'First name is required field' => array(array(
                'website_id' => 0,
                'group_id' => 1,
                'disable_auto_group_change' => 0,
                'prefix' => null,
                'firstname' => null,
                'lastname' => 'ServiceTestCreateExceptionFNR',
                'suffix' => null,
                'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
                'password' => '123123q',
                'store_id' => \Magento\Core\Model\AppInterface::ADMIN_STORE_ID
            ), 'Magento\Validator\ValidatorException'),
            'Invalid email' => array(array(
                'website_id' => 0,
                'group_id' => 1,
                'disable_auto_group_change' => 0,
                'prefix' => null,
                'firstname' => 'ServiceTestCreate',
                'lastname' => 'ExceptionInvalidEmail',
                'suffix' => null,
                'email' => '111@111',
                'password' => '123123q',
                'store_id' => \Magento\Core\Model\AppInterface::ADMIN_STORE_ID
            ), 'Magento\Validator\ValidatorException'),
            'Invalid password' => array(array(
                'website_id' => 0,
                'group_id' => 1,
                'disable_auto_group_change' => 0,
                'prefix' => null,
                'firstname' => 'ServiceTestCreate',
                'lastname' => 'ExceptionPassword',
                'suffix' => null,
                'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
                'password' => '123',
                'store_id' => \Magento\Core\Model\AppInterface::ADMIN_STORE_ID
            ), 'Magento\Eav\Model\Entity\Attribute\Exception', 'The password must have at least 6 characters.')
        );
    }

    /**
     * @param array $addressesData
     * @dataProvider createWithAddressesDataProvider
     */
    public function testCreateWithAddresses($addressesData)
    {
        $customerData = array(
            'firstname' => 'ServiceTest',
            'lastname' => 'CreateWithAddress',
            'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
        );

        $this->_createdCustomer = $this->_model->create($customerData, $addressesData);
        $this->assertCount(count($addressesData), $this->_createdCustomer->getAddresses());

        /** @var \Magento\Customer\Model\Customer $loadedCustomer */
        $loadedCustomer = $this->_customerFactory->create()
            ->load($this->_createdCustomer->getId());

        $createdData = array();
        /** @var \Magento\Customer\Model\Address $address */
        foreach ($loadedCustomer->getAddresses() as $address) {
            $addressData = current($addressesData);
            $createdData[] = $address->toArray(array_keys($addressData));
            next($addressesData);
        }

        $this->assertEquals(
            $this->_getSortedByKey($createdData, 'firstname'),
            $this->_getSortedByKey($addressesData, 'firstname')
        );
    }

    /**
     * @param array $data
     * @param string $sortKey
     * @return array
     */
    protected function _getSortedByKey($data, $sortKey)
    {
        $callback = function ($elementOne, $elementTwo) use ($sortKey) {
            return strcmp($elementOne[$sortKey], $elementTwo[$sortKey]);
        };
        usort($data, $callback);
        return $data;
    }

    /**
     * @return array
     */
    public function createWithAddressesDataProvider()
    {
        return array(
            'Two addresses' => array(
                array(
                    array(
                        'prefix' => 'Mrs.',
                        'firstname' => 'Linda',
                        'middlename' => 'G.',
                        'lastname' => 'Jones',
                        'suffix' => 'Suffix',
                        'company' => 'Vitagee',
                        'street' => "3083 Eagles Nest Drive\nHoopa",
                        'country_id' => 'US',
                        'region_id' => '12', // California
                        'city' => 'Los Angeles',
                        'postcode' => '95546',
                        'fax' => '55512450056',
                        'telephone' => '55512450000',
                        'vat_id' => '556-70-1739',
                    ),
                    array(
                        'firstname' => 'John',
                        'lastname' => 'Smith',
                        'street' => 'Green str, 67',
                        'country_id' => 'AL',
                        'city' => 'CityM',
                        'postcode' => '75477',
                        'telephone' => '3468676',
                    )
                )
            ),
            'One address' => array(
                array(
                    array(
                        'firstname' => 'John',
                        'lastname' => 'Smith',
                        'street' => 'Green str, 67',
                        'country_id' => 'AL',
                        'city' => 'CityM',
                        'postcode' => '75477',
                        'telephone' => '3468676',
                    )
                )
            )
        );
    }

    /**
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage The address with the specified ID not found.
     */
    public function testCreateWithInvalidAddressId()
    {
        $customerData = array(
            'firstname' => 'ServiceTest',
            'lastname' => 'InvalidAddress',
            'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
        );

        $addressData = array(
            'entity_id' => 'fake',
            'firstname' => 'John',
            'lastname' => 'Smith',
            'street' => 'Green str, 67',
            'country_id' => 'AL',
            'city' => 'CityM',
            'postcode' => '75477',
            'telephone' => '3468676',
        );

        $this->_createdCustomer = $this->_model->create($customerData, array($addressData));
    }

    /**
     * @param array $customerData
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @dataProvider updateDataProvider
     */
    public function testUpdate($customerData)
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
            ->getArea(\Magento\Core\Model\App\Area::AREA_FRONTEND)->load();
        $expected = $this->_customerFactory->create()
            ->load(1);

        $updatedCustomer = $this->_model->update($expected->getId(), $customerData);
        $this->assertInstanceOf('Magento\Customer\Model\Customer', $updatedCustomer);
        $this->assertFalse($updatedCustomer->isObjectNew());

        $actualData = $this->_customerFactory->create()
            ->load($expected->getId())->getData();
        $expectedData = array_merge($updatedCustomer->toArray(array_keys($actualData)), $customerData);
        unset($expectedData['updated_at'], $actualData['updated_at']);
        if (isset($expectedData['password'])) {
            // TODO Add assertions for password if needed
            unset($expectedData['password'], $actualData['password']);
        }
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * @return array
     */
    public function updateDataProvider()
    {
        return array(
            'Change name' => array(array(
                'firstname' => 'SomeName2',
            )),
            'Change password' => array(array(
                'password' => '111111'
            )),
            'Multiple properties' => array(array(
                'disable_auto_group_change' => 0,
                'prefix' => 'Prefix',
                'firstname' => 'ServiceTest',
                'middlename' => 'Middlename',
                'lastname' => 'UpdateMultiple',
                'suffix' => 'Suffix',
                'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
                'dob' => date('Y-m-d H:i:s'),
                'gender' => 1,
                'store_id' => \Magento\Core\Model\AppInterface::ADMIN_STORE_ID
            ))
        );
    }

    /**
     * @param array $customerData
     * @param string $exceptionName
     * @param string $exceptionMessage
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @dataProvider updateExceptionsDataProvider
     */
    public function testUpdateExceptions($customerData, $exceptionName, $exceptionMessage = '')
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
            ->getArea(\Magento\Core\Model\App\Area::AREA_FRONTEND)->load();
        $this->setExpectedException($exceptionName, $exceptionMessage);
        $this->_model->update(1, $customerData);
    }

    /**
     * @return array
     */
    public function updateExceptionsDataProvider()
    {
        return array(
            'Invalid password' => array(array(
                'password' => '111'
            ), 'Magento\Eav\Model\Entity\Attribute\Exception'),
            'Invalid name' => array(array(
                'firstname' => null
            ), 'Magento\Validator\ValidatorException'),
            'Invalid email' => array(array(
                'email' => '3434@23434'
            ), 'Magento\Validator\ValidatorException')
        );
    }

    /**
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage The customer with the specified ID not found.
     */
    public function testUpdateInvalidCustomerId()
    {
        $this->_model->update(1, array('firstname' => 'Foo'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testAutoGeneratePassword()
    {
        $oldPasswordHash = $this->_customerFactory->create()
            ->load(1)
            ->getPasswordHash();
        $updatedCustomer = $this->_model->update(1, array(
            'autogenerate_password' => true,
        ));
        $this->assertNotEquals($oldPasswordHash, $updatedCustomer->getPasswordHash());
    }

    /**
     * @param array $addressesData
     * @dataProvider customerAddressDataProvider
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testCustomerAddressManipulation($addressesData)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->_customerFactory->create()
            ->load(1);
        $this->assertCount(2, $customer->getAddresses(), 'Not all customer addresses were created.');
        $updatedCustomer = $this->_model->update(1, array(), $addressesData);
        $this->assertCount(count($addressesData), $updatedCustomer->getAddresses(),
            'Customer address was not deleted.');

        /** @var \Magento\Customer\Model\Customer $actualCustomer */
        $actualCustomer = $this->_customerFactory->create()
            ->load(1);
        $actualAddresses = $actualCustomer->getAddresses();
        $this->assertCount(count($addressesData), $actualAddresses, 'Customer address was not actually deleted.');

        // Check that all addresses were updated correctly
        $updatedData = array();
        /** @var \Magento\Customer\Model\Address $address */
        $addressesData = $this->_getSortedByKey($addressesData, 'postcode');
        foreach ($this->_getSortedByKey($actualAddresses, 'postcode') as $address) {
            $addressData = current($addressesData);
            $updatedData[] = $address->toArray(array_keys($addressData));
            next($addressesData);
        }
        $this->assertEquals($addressesData, $updatedData, 'Customer addresses are incorrect.');
    }

    /**
     * @return array
     */
    public function customerAddressDataProvider()
    {
        return array(
            'Addresses update' => array(
                array(
                    array('entity_id' => 1, 'postcode' => '1000001'),
                    array('entity_id' => 2, 'postcode' => '1000002')
                )
            ),
            'First address delete' => array(
                array(
                    array('entity_id' => 2)
                )
            ),
            'First address update, second delete' => array(
                array(
                    array('entity_id' => 1, 'city' => 'Updated city', 'postcode' => '1000001')
                )
            ),
            'All addresses delete' => array(
                array()
            ),
            'Addresses updated and one created' => array(
                array(
                    array('entity_id' => 1, 'postcode' => '1000001'),
                    array('entity_id' => 2, 'city' => 'Updated city', 'postcode' => '1000002')
                )
            ),
            'Address updated, deleted and created' => array(
                array(
                    array(
                        'firstname' => 'John',
                        'lastname' => 'Smith',
                        'street' => 'Green str, 67',
                        'country_id' => 'AL',
                        'city' => 'CityM',
                        'postcode' => '1000001',
                        'telephone' => '3468676'
                    ),
                    array('entity_id' => 2, 'city' => 'Updated city', 'postcode' => '1000002')
                )
            )
        );
    }

    /**
     * Test beforeSave and afterSave callback
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCallback()
    {
        $customer = $this->_customerFactory->create()
            ->load(1);
        $customerData = array('firstname' => 'Updated name');
        $customer->addData($customerData);
        $addressData = array(array(
            'firstname' => 'John',
            'lastname' => 'Smith',
            'street' => 'Green str, 67',
            'country_id' => 'AL',
            'city' => 'CityM',
            'postcode' => '75477',
            'telephone' => '3468676',
        ));

        $callbackCount = 0;
        $callback = function ($actualCustomer, $actualData, $actualAddresses) use ($customer, $customerData,
            $addressData, &$callbackCount
        ) {
            $callbackCount++;
            // Remove updated_at as in afterSave updated_at may be changed
            $expectedCustomerData = $customer->getData();
            unset($expectedCustomerData['updated_at']);
            \PHPUnit_Framework_Assert::assertEquals($expectedCustomerData,
                $actualCustomer->toArray(array_keys($expectedCustomerData)));
            \PHPUnit_Framework_Assert::assertEquals($customerData, $actualData);
            \PHPUnit_Framework_Assert::assertEquals($addressData, $actualAddresses);
        };

        $this->_model->setBeforeSaveCallback($callback);
        $this->_model->setAfterSaveCallback($callback);
        $this->_model->update(1, $customerData, $addressData);
        $this->assertEquals(2, $callbackCount, 'Not all expected callbacks were called.');
    }

    /**
     * @param bool $isAdminStore
     * @param boolean $isConfirmed
     * @dataProvider forceConfirmedDataProvider
     */
    public function testCustomerSetForceConfirmed($isAdminStore, $isConfirmed)
    {
        $this->_model->setIsAdminStore($isAdminStore);
        $customerData = array(
            'firstname' => 'ServiceTest',
            'lastname' => 'ForceConfirmed',
            'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
            'password' => '123123q'
        );
        $this->_createdCustomer = $this->_model->create($customerData);
        $this->assertEquals($isConfirmed, $this->_createdCustomer->getForceConfirmed());
    }

    /**
     * @return array
     */
    public function forceConfirmedDataProvider()
    {
        return array(
            'admin store' => array(true, true),
            'distro store' => array(false, false),
        );
    }
}
