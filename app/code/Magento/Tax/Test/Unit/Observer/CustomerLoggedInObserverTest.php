<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Observer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Data\Group;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Observer\CustomerLoggedInObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerLoggedInObserverTest extends TestCase
{
    /**
     * @var Observer
     */
    protected $observerMock;

    /**
     * @var Session
     */
    protected $customerSessionMock;

    /**
     * @var GroupRepository
     */
    protected $groupRepositoryMock;

    /**
     * Module manager
     *
     * @var Manager
     */
    private $moduleManagerMock;

    /**
     * Cache config
     *
     * @var Config
     */
    private $cacheConfigMock;

    /**
     * @var Data
     */
    protected $taxHelperMock;

    /**
     * @var TaxAddressManagerInterface|MockObject
     */
    private $addressManagerMock;

    /**
     * @var CustomerLoggedInObserver
     */
    protected $session;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerAddress'])
            ->onlyMethods(['getData'])
            ->getMock();

        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'setCustomerTaxClassId',
                    'setDefaultTaxBillingAddress',
                    'setDefaultTaxShippingAddress',
                    'setWebsiteId'
                ]
            )
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressManagerMock = $this->getMockBuilder(TaxAddressManagerInterface::class)
            ->onlyMethods(['setDefaultAddressAfterSave', 'setDefaultAddressAfterLogIn'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->session = $objectManager->getObject(
            CustomerLoggedInObserver::class,
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

        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observerMock->expects($this->once())
            ->method('getData')
            ->with('customer')
            ->willReturn($customerMock);

        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn(1);

        /* @var \Magento\Customer\Api\Data\AddressInterface|MockObject $address */
        $address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);

        $customerGroupMock = $this->getMockBuilder(Group::class)
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
