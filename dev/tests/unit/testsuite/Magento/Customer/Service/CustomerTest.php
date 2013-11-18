<?php
/**
 * \Magento\Customer\Service\Customer
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
     * @var \Magento\Customer\Service\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_addressFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customer;

    /** @var \Magento\Customer\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_address;

    protected function setUp()
    {
        $this->_helperMock = $this->getMockBuilder('Magento\Customer\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array('generateResetPasswordLinkToken'))
            ->getMock();

        $this->_customerFactory = $this->getMockBuilder('Magento\Customer\Model\CustomerFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $this->_addressFactory = $this->getMockBuilder('Magento\Customer\Model\AddressFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();

        $this->_customer = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->setMethods(array('save', 'generatePassword', 'getOrigData', 'sendNewAccountEmail', 'getConfirmation',
                'getPrimaryAddress', 'getAddresses', 'getAdditionalAddresses', 'load', 'getId', 'changePassword',
                'sendPasswordReminderEmail', 'addAddress', 'getAddressItemById', 'getAddressesCollection',
                'hashPassword', 'changeResetPasswordLinkToken', '__wakeup')
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->_customerFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customer));

        $this->_address = $this->_createAddress(true, null);
        $this->_addressFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_address));

        $this->_service = new \Magento\Customer\Service\Customer($this->_helperMock, $this->_customerFactory,
            $this->_addressFactory
        );
    }

    /**
     * Create mock address for use in tests
     *
     * @param bool $hasChanges
     * @param int $addressId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createAddress($hasChanges, $addressId)
    {
        $address = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->setMethods(array('hasDataChanges', 'getId', 'addData', 'setData', 'setCustomerId', 'setPostIndex',
                '__sleep', '__wakeup'))
            ->getMock();
        $address->expects($this->any())
            ->method('hasDataChanges')
            ->will($this->returnValue($hasChanges));
        if ($addressId) {
            $address->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($addressId));
        }
        return $address;
    }

    protected function tearDown()
    {
        unset($this->_service);
    }

    /**
     * Test for setSendRemainderEmail
     *
     * @param bool $set
     * @param bool $expected
     * @dataProvider setSendRemainderDataProvider
     */
    public function testSetSendRemainderEmail($set, $expected)
    {
        $this->_service->setSendRemainderEmail($set);
        $this->assertAttributeEquals($expected, '_sendRemainderEmail', $this->_service);
    }

    /**
     * @return array
     */
    public function setSendRemainderDataProvider()
    {
        return array(
            array(true, true),
            array(false, false)
        );
    }

    /**
     * Test beforeSave and afterSave callback are set correctly
     */
    public function testSetBeforeSaveCallback()
    {
        $this->assertInstanceOf('Magento\Customer\Service\Customer', $this->_service->setBeforeSaveCallback('intval'));
        $this->assertAttributeEquals('intval', '_beforeSaveCallback', $this->_service);
    }

    /**
     * Check beforeSave and afterSave callbacks for create and update methods
     *
     * @param string $method
     * @dataProvider methodsDataProvider
     */
    public function testCallback($method)
    {
        $customerData = array('firstname' => 'test');
        $addressData = null;
        $callback = $this->getMockBuilder('stdClass')
            ->setMethods(array('beforeSave', 'afterSave'))
            ->getMock();
        $callback->expects($this->once())
            ->method('beforeSave')
            ->with($this->_customer, $customerData, $addressData);
        $callback->expects($this->once())
            ->method('afterSave')
            ->with($this->_customer, $customerData, $addressData);

        $this->_service->setBeforeSaveCallback(array($callback, 'beforeSave'));
        $this->_service->setAfterSaveCallback(array($callback, 'afterSave'));
        if ($method == 'create') {
            $this->assertInstanceOf('Magento\Customer\Model\Customer',
                $this->_service->create($customerData, $addressData));
        } else {
            $this->_customer->expects($this->once())
                ->method('getId')
                ->will($this->returnValue(1));
            $this->assertInstanceOf('Magento\Customer\Model\Customer',
                $this->_service->update(1, $customerData, $addressData));
        }
    }

    /**
     * @return array
     */
    public function methodsDataProvider()
    {
        return array(
            array('create'),
            array('update')
        );
    }

    public function testSetAfterSaveCallback()
    {
        $this->assertInstanceOf('Magento\Customer\Service\Customer', $this->_service->setAfterSaveCallback('intval'));
        $this->assertAttributeEquals('intval', '_afterSaveCallback', $this->_service);
    }

    /**
     * Test setIsAdminStore setter
     */
    public function testSetIsAdminStore()
    {
        $this->assertInstanceOf('Magento\Customer\Service\Customer', $this->_service->setIsAdminStore(true));
        $this->assertAttributeEquals(true, '_isAdminStore', $this->_service);
    }

    /**
     * @param bool $isAdminStore
     * @param array $customerData
     * @param array $expectedData
     * @dataProvider forceConfirmedDataProvider
     */
    public function testCreateForceConfirmed($isAdminStore, array $customerData, array $expectedData)
    {
        if (array_key_exists('autogenerate_password', $customerData)) {
            $this->_customer->expects($this->once())
                ->method('generatePassword')
                ->will($this->returnValue('generated_password'));
        }
        
        $this->_customer->expects($this->once())
            ->method('save');

        $this->_service->setIsAdminStore($isAdminStore);
        $this->assertInstanceOf('Magento\Customer\Model\Customer',
            $this->_service->create($customerData));
        $this->assertEquals($expectedData, $this->_customer->toArray(array_keys($expectedData)));
    }

    /**
     * @return array
     */
    public function forceConfirmedDataProvider()
    {
        return array(
            'force confirmed not set #1' => array(
                'isAdminStore' => false,
                'customerData' => array(
                    'password' => '123123q'
                ),
                'expectedData' => array(
                    'password' => '123123q',
                    'force_confirmed' => null
                ),
            ),
            'force confirmed not set #2' => array(
                'isAdminStore' => true,
                'customerData' => array(),
                'expectedData' => array(
                    'force_confirmed' => null
                ),
            ),
            'force confirmed is set' => array(
                'isAdminStore' => true,
                'customerData' => array(
                    'password' => '123123q'
                ),
                'expectedData' => array(
                    'password' => '123123q',
                    'force_confirmed' => true
                ),
            ),
            'auto generated password' => array(
                'isAdminStore' => true,
                'customerData' => array(
                    'autogenerate_password' => true
                ),
                'expectedData' => array(
                    'password' => 'generated_password',
                    'force_confirmed' => true
                ),
            )
        );
    }

    /**
     * @param array $customerData
     * @param string $type
     * @param int|null $origId
     * @dataProvider welcomeEmailDataProvider
     */
    public function testSendWelcomeEmail(array $customerData, $type, $origId)
    {
        $this->_customer->expects($this->once())
            ->method('sendNewAccountEmail')
            ->with($type, '', $customerData['sendemail_store_id']);
        $this->_customer->expects($this->once())
            ->method('save');
        $this->_customer->expects($this->once())
            ->method('getOrigData')
            ->will($this->returnValue($origId));
        $this->_customer->expects($this->any())
            ->method('getConfirmation')
            ->will($this->returnValue(false));

        $this->assertInstanceOf('Magento\Customer\Model\Customer', $this->_service->create($customerData));
    }

    /**
     * Test that email is send for registered users
     */
    public function testSendWelcomeEmailRegistered()
    {
        $storeId = 1;
        $type = 'registered';
        $customerData = array(
            'sendemail_store_id' => $storeId,
            'website_id' => 1,
            'sendemail' => true,
            'autogenerate_password' => true
        );
        $hash = 1234567890;

        $this->_customer->expects($this->once())
            ->method('sendNewAccountEmail')
            ->with($type, '', $storeId);
        $this->_customer->expects($this->once())
            ->method('save');
        $this->_customer->expects($this->any())
            ->method('getOrigData')
            ->with($this->equalTo('id'))
            ->will($this->returnValue(false));

        $this->_helperMock->expects($this->once())
            ->method('generateResetPasswordLinkToken')
            ->will($this->returnValue($hash));

        $this->_customer->expects($this->once())->method('changeResetPasswordLinkToken')->with($this->equalTo($hash));
        $this->_customer->expects($this->once())
            ->method('sendNewAccountEmail')
            ->with(
                $this->equalTo($type),
                $this->equalTo(''),
                $this->equalTo($storeId)
            );

        $this->assertInstanceOf('Magento\Customer\Model\Customer', $this->_service->create($customerData));
    }

    /**
     * Test that email is send in case when user is confirmed
     */
    public function testSendWelcomeEmailConfirmed()
    {
        $storeId = 1;
        $type = 'confirmed';
        $customerData = array(
            'sendemail_store_id' => $storeId,
            'website_id' => 1,
            'sendemail' => true,
            'autogenerate_password' => true
        );

        $this->_customer->expects($this->once())
            ->method('sendNewAccountEmail')
            ->with($type, '', $storeId);
        $this->_customer->expects($this->once())
            ->method('save');
        $this->_customer->expects($this->any())
            ->method('getOrigData')
            ->with($this->equalTo('id'))
            ->will($this->returnValue(true));

        $this->_customer->expects($this->once())
            ->method('sendNewAccountEmail')
            ->with(
                $this->equalTo($type),
                $this->equalTo(''),
                $this->equalTo($storeId)
            );

        $this->assertInstanceOf('Magento\Customer\Model\Customer', $this->_service->create($customerData));
    }

    /**
     * @return array
     */
    public function welcomeEmailDataProvider()
    {
        return array(
            'new customer new password' => array(
                array(
                    'sendemail_store_id' => 1,
                    'website_id' => 1,
                    'sendemail' => true
                ),
                'registered',
                null
            ),
            'new customer auto generated password' => array(
                array(
                    'sendemail_store_id' => 1,
                    'website_id' => 1,
                    'autogenerate_password' => true
                ),
                'registered',
                null
            ),
            'existing customer new password' => array(
                array(
                    'sendemail_store_id' => 1,
                    'website_id' => 1,
                    'sendemail' => true
                ),
                'confirmed',
                1
            ),
            'existing customer auto generated password' => array(
                array(
                    'sendemail_store_id' => 1,
                    'website_id' => 1,
                    'autogenerate_password' => true
                ),
                'confirmed',
                1
            ),
        );
    }

    /**
     * @param array $customerData
     * @param int|null $origId
     * @dataProvider welcomeEmailNotCalledDataProvider
     */
    public function testSendWelcomeEmailNotCalled(array $customerData, $origId)
    {
        $this->_customer->expects($this->never())
            ->method('sendNewAccountEmail');
        $this->_customer->expects($this->once())
            ->method('save');
        $this->_customer->expects($this->any())
            ->method('getOrigData')
            ->will($this->returnValue($origId));
        if (array_key_exists('confirmation', $customerData)) {
            $this->_customer->expects($this->any())
               ->method('getConfirmation')
               ->will($this->returnValue($customerData['confirmation']));
        }

        $this->assertInstanceOf('Magento\Customer\Model\Customer', $this->_service->create($customerData));
    }

    /**
     * @return array
     */
    public function welcomeEmailNotCalledDataProvider()
    {
        return array(
            'unchanged password' => array(
                array(
                    'website_id' => 1,
                ),
                null
            ),
            'no website new customer send email' => array(
                array(
                    'website_id' => 0,
                    'sendemail' => true
                ),
                null
            ),
            'no website new customer auto generated password' => array(
                array(
                    'website_id' => 0,
                    'autogenerate_password' => true
                ),
                null
            ),
            'no website existing customer new password' => array(
                array(
                    'website_id' => 0,
                    'sendemail' => true
                ),
                1
            ),
            'existing customer auto generated password' => array(
                array(
                    'website_id' => 0,
                    'autogenerate_password' => true
                ),
                1
            ),
            'not new no confirmation' => array(
                array(
                    'sendemail_store_id' => 1,
                    'website_id' => 1,
                    'sendemail' => true,
                    'confirmation' => true
                ),
                1
            )
        );
    }

    /**
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage The customer with the specified ID not found.
     */
    public function testLoadCustomerByIdException()
    {
        $this->_customer->expects($this->never())
            ->method('save');
        $this->_customer->expects($this->once())
            ->method('load')
            ->with(1);
        $this->_customer->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(false));

        $this->_service->update(1, array('firstname' => 'test'));
    }

    /**
     * @param array $customerData
     * @dataProvider changePasswordDataProvider
     */
    public function testChangePassword(array $customerData)
    {
        $password = isset($customerData['password']) ? $customerData['password'] : 'generated_password';
        $this->_customer->expects($this->once())
            ->method('save');
        $this->_customer->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->_customer->expects($this->once())
            ->method('changePassword')
            ->with($password)
            ->will($this->returnSelf());
        $this->_customer->expects($this->once())
            ->method('sendPasswordReminderEmail')
            ->will($this->returnSelf());
        if (array_key_exists('autogenerate_password', $customerData)) {
            $this->_customer->expects($this->once())
                ->method('generatePassword')
                ->will($this->returnValue('generated_password'));
        }

        $this->assertInstanceOf('Magento\Customer\Model\Customer', $this->_service->update(1, $customerData));
    }

    /**
     * @return array
     */
    public function changePasswordDataProvider()
    {
        return array(
            'new password' => array(
                array('password' => '123123q')
            ),
            'auto generated password' => array(
                array('autogenerate_password' => true)
            )
        );
    }

    /**
     * @param array $addressData
     * @param \Magento\Customer\Model\Address|null $newAddress
     * @param \Magento\Customer\Model\Address|null $existingAddress
     * @param array $addressCollection
     * @param bool $dataChanged
     * @param bool $expectedDataChange
     * @dataProvider addressesDataProvider
     */
    public function testPrepareCustomerAddressForSave(array $addressData, $newAddress, $existingAddress,
        array $addressCollection, $dataChanged, $expectedDataChange
    ) {
        $this->_customer->setDataChanges($dataChanged);

        $this->_customer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $this->_customer->expects($this->once())
            ->method('getAddressesCollection')
            ->will($this->returnValue($addressCollection));

        if ($newAddress) {
            // Check that customer_id is set for new addresses
            $newAddress->expects($this->once())
                ->method('setCustomerId')
                ->with(1);
            // Check that new address is added to customer address collection
            $this->_customer->expects($this->once())
                ->method('addAddress');

            // Check that new address is not deleted
            $newAddress->expects($this->never())
                ->method('setData')
                ->with('_deleted', true);
        }

        // Check that address loaded from customer address collection
        if ($existingAddress && $addressData) {
            $this->_customer->expects($this->once())
                ->method('getAddressItemById')
                ->with($existingAddress->getId())
                ->will($this->returnValue($existingAddress));
        }

        // Check that new data is added
        $hasExistingAddress = false;
        foreach ($addressData as $data) {
            if (array_key_exists('entity_id', $addressData)) {
                unset($data['entity_id']);
                $existingAddress->expects($this->once())
                    ->method('addData')
                    ->with($data);
                $hasExistingAddress = true;
            } elseif ($newAddress) {
                $newAddress->expects($this->once())
                    ->method('addData')
                    ->with($data);
            }
        }
        // Check that addresses is deleted
        if (in_array($existingAddress, $addressCollection) && !$hasExistingAddress) {
            $existingAddress->expects($this->atLeastOnce())
                ->method('setData')
                ->with('_deleted', true);
        }

        $this->assertInstanceOf('Magento\Customer\Model\Customer', $this->_service->update(1, array(), $addressData),
            'Incorrect instance returned');

        $this->assertEquals($expectedDataChange, $this->_customer->hasDataChanges(),
            'Customer change data status is incorrect');
    }

    /**
     * @return array
     */
    public function addressesDataProvider()
    {
        $newAddress = $this->_createAddress(true, null);
        $existingAddress = $this->_createAddress(true, 2);
        return array(
            'no addresses #1' => array(
                array(), null, null, array(), false, false
            ),
            'no addresses #2' => array(
                array(), null, null, array(), true, true
            ),
            'new address' => array(
                array(array('city' => 'test')),
                $newAddress, null,
                array($newAddress), false, true
            ),
            'existing address' => array(
                array(array('entity_id' => 2, 'city' => 'test')),
                null, $existingAddress,
                array($existingAddress), false, true
            ),
            'new added, existing deleted' => array(
                array(array('city' => 'test')),
                $newAddress, null,
                array($newAddress, $existingAddress), false, true
            ),
            'all addresses deleted' => array(
                array(),
                null, $existingAddress,
                array($existingAddress), false, true
            )
        );
    }

    /**
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage The address with the specified ID not found.
     */
    public function testPrepareCustomerAddressForSaveException()
    {
        $this->_customer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $address = $this->_createAddress(true, 1);
        $this->_customer->expects($this->once())
            ->method('getAddressItemById')
            ->with($address->getId())
            ->will($this->returnValue(false));
        $this->_service->update(1, array(), array(array('entity_id' => 1, 'city' => 'test')));
    }
}
