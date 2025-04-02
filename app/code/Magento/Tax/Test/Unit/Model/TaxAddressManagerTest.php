<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\TaxAddressManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxAddressManagerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TaxAddressManager
     */
    private $manager;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['setDefaultTaxBillingAddress', 'setDefaultTaxShippingAddress'])
            ->getMock();

        $this->manager = $this->objectManager->getObject(
            TaxAddressManager::class,
            [
                'customerSession' => $this->customerSessionMock,
            ]
        );
    }

    /**
     * @test
     * @dataProvider setAddressCustomerSessionAddressSaveDataProvider
     *
     * @param array $addressId
     * @param array $billingInfo
     * @param array $shippingInfo
     * @param bool $needSetShipping
     * @param bool $needSetBilling
     */
    public function testSetDefaultAddressAfterSave(
        $addressId,
        $billingInfo,
        $shippingInfo,
        $needSetShipping,
        $needSetBilling
    ) {
        list($customerDefBillAddId, $isPrimaryBilling, $isDefaultBilling) = $billingInfo;
        list($customerDefShipAddId, $isPrimaryShipping, $isDefaultShipping) = $shippingInfo;

        /* @var \Magento\Customer\Model\Address|MockObject $address */
        $address = $this->getMockBuilder(Address::class)
            ->addMethods([
                'getIsPrimaryBilling',
                'getIsDefaultBilling',
                'getIsPrimaryShipping',
                'getIsDefaultShipping',
                'getCountryId',
                'getPostcode'
            ])
            ->onlyMethods([
                'getId',
                'getCustomer',
                'getRegion',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $address->expects($this->any())->method('getCountryId')->willReturn(1);
        $address->expects($this->any())->method('getRegion')->willReturn(null);
        $address->expects($this->any())->method('getPostcode')->willReturn('11111');

        $address->expects($this->any())->method('getId')->willReturn($addressId);
        $address->expects($this->any())->method('getIsPrimaryBilling')->willReturn($isPrimaryBilling);
        $address->expects($this->any())->method('getIsDefaultBilling')->willReturn($isDefaultBilling);
        $address->expects($this->any())->method('getIsPrimaryShipping')->willReturn($isPrimaryShipping);
        $address->expects($this->any())->method('getIsDefaultShipping')->willReturn($isDefaultShipping);

        /* @var \Magento\Customer\Model\Customer|MockObject $customer */
        $customer = $this->getMockBuilder(Customer::class)
            ->addMethods(['getDefaultBilling', 'getDefaultShipping'])
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->any())->method('getDefaultBilling')->willReturn($customerDefBillAddId);
        $customer->expects($this->any())->method('getDefaultShipping')->willReturn($customerDefShipAddId);

        $address->expects($this->any())->method('getCustomer')->willReturn($customer);

        $this->customerSessionMock->expects($needSetShipping ? $this->once() : $this->never())
            ->method('setDefaultTaxShippingAddress')
            ->with(['country_id' => 1, 'region_id' => null, 'postcode' => 11111]);
        $this->customerSessionMock->expects($needSetBilling ? $this->once() : $this->never())
            ->method('setDefaultTaxBillingAddress')
            ->with(['country_id' => 1, 'region_id' => null, 'postcode' => 11111]);

        $this->manager->setDefaultAddressAfterSave($address);
    }

    /**
     * @return array
     */
    public static function setAddressCustomerSessionAddressSaveDataProvider()
    {
        return [
            [1, [1, false, false], [1, false, false], true, true],
            [1, [2, false, false], [2, false, false], false, false],
            [1, [2, false, true], [2, false, true], true, true],
            [1, [2, true, false], [2, true, false], true, true],
        ];
    }

    /**
     * @test
     * @dataProvider setAddressCustomerSessionLogInDataProvider
     *
     * @param bool $isAddressDefaultBilling
     * @param bool $isAddressDefaultShipping
     */
    public function testSetDefaultAddressAfterLogIn(
        $isAddressDefaultBilling,
        $isAddressDefaultShipping
    ) {
        /* @var \Magento\Customer\Api\Data\AddressInterface|MockObject $address */
        $address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $address->expects($this->any())->method('getCountryId')->willReturn(1);
        $address->expects($this->any())->method('getRegion')->willReturn(null);
        $address->expects($this->any())->method('getPostcode')->willReturn('11111');
        $address->expects($this->any())->method('isDefaultShipping')->willReturn($isAddressDefaultShipping);
        $address->expects($this->any())->method('isDefaultBilling')->willReturn($isAddressDefaultBilling);

        $this->customerSessionMock->expects($isAddressDefaultShipping ? $this->once() : $this->never())
            ->method('setDefaultTaxShippingAddress')
            ->with(['country_id' => 1, 'region_id' => null, 'postcode' => 11111]);
        $this->customerSessionMock->expects($isAddressDefaultBilling ? $this->once() : $this->never())
            ->method('setDefaultTaxBillingAddress')
            ->with(['country_id' => 1, 'region_id' => null, 'postcode' => 11111]);

        $this->manager->setDefaultAddressAfterLogIn([$address]);
    }

    /**
     * @return array
     */
    public static function setAddressCustomerSessionLogInDataProvider()
    {
        return [
            [false, false],
            [false, true],
            [true, false],
            [true, true],
        ];
    }
}
