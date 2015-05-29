<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\Observer;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $observerMock;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Customer\Model\Resource\GroupRepository
     */
    protected $groupRepositoryMock;

    /**
     * @var \Magento\Tax\Model\Observer\Session
     */
    protected $session;


    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupRepositoryMock = $this->getMockBuilder('Magento\Customer\Model\Resource\GroupRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([
                'setCustomerTaxClassId', 'setDefaultTaxBillingAddress', 'setDefaultTaxShippingAddress'
            ])
            ->getMock();

        $this->session = $this->objectManager->getObject(
            'Magento\Tax\Model\Observer\Session',
            [
                'groupRepository' => $this->groupRepositoryMock,
                'customerSession' => $this->customerSessionMock
            ]
        );
    }

    public function testCustomerLoggedIn()
    {
        $customerMock = $this->getMockBuilder('Magento\Customer\Model\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->observerMock->expects($this->once())
            ->method('getData')
            ->with('customer')
            ->willReturn($customerMock);

        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn(1);

        $customerGroupMock = $this->getMockBuilder('Magento\Customer\Model\Data\Group')
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($customerGroupMock);

        $customerGroupMock->expects($this->once())
            ->method('getTaxClassId')
            ->willReturn(1);

        $this->customerSessionMock->expects($this->once())
            ->method('setCustomerTaxClassId')
            ->with(1);

        $address = $this->objectManager->getObject('Magento\Customer\Model\Data\Address');
        $address->setIsDefaultShipping(true);
        $address->setIsDefaultBilling(true);
        $address->setCountryId(1);
        $address->setPostCode(11111);

        $addresses = [$address];
        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn($addresses);

        $this->customerSessionMock->expects($this->once())
            ->method('setDefaultTaxBillingAddress')
            ->with(['country_id' => 1, 'region_id' => null, 'postcode' => 11111]);
        $this->customerSessionMock->expects($this->once())
            ->method('setDefaultTaxShippingAddress')
            ->with(['country_id' => 1, 'region_id' => null, 'postcode' => 11111]);

        $this->session->customerLoggedIn($this->observerMock);
    }
}
