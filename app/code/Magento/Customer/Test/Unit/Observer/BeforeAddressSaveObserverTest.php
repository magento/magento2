<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Helper\Address as HelperAddress;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Observer\BeforeAddressSaveObserver;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Registry;

class BeforeAddressSaveObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Observer\BeforeAddressSaveObserver
     */
    protected $model;

    /**
     * @var Registry |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var HelperAddress |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperAddress;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperAddress = $this->getMockBuilder(\Magento\Customer\Helper\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new BeforeAddressSaveObserver(
            $this->helperAddress,
            $this->registry
        );
    }

    public function testBeforeAddressSaveWithCustomerAddressId()
    {
        $customerAddressId = 1;

        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($customerAddressId);

        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->registry->expects($this->once())
            ->method('registry')
            ->with(BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS)
            ->willReturn(true);
        $this->registry->expects($this->once())
            ->method('unregister')
            ->with(BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS)
            ->willReturnSelf();
        $this->registry->expects($this->once())
            ->method('register')
            ->with(BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS, $customerAddressId)
            ->willReturnSelf();

        $this->model->execute($observer);
    }

    /**
     * @param string $configAddressType
     * @param $isDefaultBilling
     * @param $isDefaultShipping
     * @dataProvider dataProviderBeforeAddressSaveWithoutCustomerAddressId
     */
    public function testBeforeAddressSaveWithoutCustomerAddressId(
        $configAddressType,
        $isDefaultBilling,
        $isDefaultShipping
    ) {
        $customerAddressId = null;

        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getIsDefaultBilling', 'getIsDefaultShipping', 'setForceProcess'])
            ->getMock();
        $address->expects($this->once())
            ->method('getId')
            ->willReturn($customerAddressId);
        $address->expects($this->any())
            ->method('getIsDefaultBilling')
            ->willReturn($isDefaultBilling);
        $address->expects($this->any())
            ->method('getIsDefaultShipping')
            ->willReturn($isDefaultShipping);
        $address->expects($this->any())
            ->method('setForceProcess')
            ->with(true)
            ->willReturnSelf();

        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->helperAddress->expects($this->once())
            ->method('getTaxCalculationAddressType')
            ->willReturn($configAddressType);

        $this->registry->expects($this->once())
            ->method('registry')
            ->with(BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS)
            ->willReturn(true);
        $this->registry->expects($this->once())
            ->method('unregister')
            ->with(BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS)
            ->willReturnSelf();
        $this->registry->expects($this->any())
            ->method('register')
            ->willReturnMap([
                [BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS, $customerAddressId, false, $this->registry],
                [BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS, 'new_address', false, $this->registry],
            ]);

        $this->model->execute($observer);
    }

    /**
     * @return array
     */
    public function dataProviderBeforeAddressSaveWithoutCustomerAddressId()
    {
        return [
            [
                'TaxCalculationAddressType' => AbstractAddress::TYPE_BILLING,
                'isDefaultBilling' => true,
                'isDefaultShipping' => false,
            ],
            [
                'TaxCalculationAddressType' => AbstractAddress::TYPE_SHIPPING,
                'isDefaultBilling' => false,
                'isDefaultShipping' => true,
            ],
        ];
    }
}
