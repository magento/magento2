<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Observer;

use Magento\Tax\Api\TaxAddressManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Customer logged in observer test
 */
class CustomerLoggedInObserverTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Customer\Model\ResourceModel\GroupRepository
     */
    protected $groupRepositoryMock;

    /**
     * Module manager
     *
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManagerMock;

    /**
     * Cache config
     *
     * @var \Magento\PageCache\Model\Config
     */
    private $cacheConfigMock;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelperMock;

    /**
     * @var TaxAddressManagerInterface|MockObject
     */
    private $addressManagerMock;

    /**
     * @var \Magento\Tax\Observer\CustomerLoggedInObserver
     */
    protected $session;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                'getCustomerAddress', 'getData'
                ]
            )
            ->getMock();

        $this->groupRepositoryMock = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\GroupRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                'setCustomerTaxClassId', 'setDefaultTaxBillingAddress', 'setDefaultTaxShippingAddress', 'setWebsiteId'
                ]
            )
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(\Magento\Framework\Module\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder(\Magento\PageCache\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxHelperMock = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressManagerMock = $this->getMockBuilder(TaxAddressManagerInterface::class)
            ->setMethods(['setDefaultAddressAfterSave', 'setDefaultAddressAfterLogIn'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->session = $objectManager->getObject(
            \Magento\Tax\Observer\CustomerLoggedInObserver::class,
            [
                'groupRepository' => $this->groupRepositoryMock,
                'customerSession' => $this->customerSessionMock,
                'taxHelper' => $this->taxHelperMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock,
                'addressManager' => $this->addressManagerMock,
            ]
        );
    }

    /**
     * @test
     */
    public function testExecute()
    {
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn(true);

        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->taxHelperMock->expects($this->any())
            ->method('isCatalogPriceDisplayAffectedByTax')
            ->willReturn(true);

        $customerMock = $this->getMockBuilder(\Magento\Customer\Model\Data\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observerMock->expects($this->once())
            ->method('getData')
            ->with('customer')
            ->willReturn($customerMock);

        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn(1);

        /* @var \Magento\Customer\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);

        $customerGroupMock = $this->getMockBuilder(\Magento\Customer\Model\Data\Group::class)
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

        $this->addressManagerMock->expects($this->once())
            ->method('setDefaultAddressAfterLogIn')
            ->with([$address]);

        $this->session->execute($this->observerMock);
    }
}
