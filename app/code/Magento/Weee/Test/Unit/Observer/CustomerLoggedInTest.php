<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Observer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Weee\Helper\Data;
use Magento\Weee\Observer\CustomerLoggedIn;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerLoggedInTest extends TestCase
{
    /**
     * @var Observer
     */
    protected $observerMock;

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
    protected $weeeHelperMock;

    /**
     * @var TaxAddressManagerInterface|MockObject
     */
    private $addressManagerMock;

    /**
     * @var CustomerLoggedIn
     */
    protected $session;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->addMethods(['getCustomerAddress'])
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressManagerMock = $this->getMockBuilder(TaxAddressManagerInterface::class)
            ->onlyMethods(['setDefaultAddressAfterSave', 'setDefaultAddressAfterLogIn'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->session = $objectManager->getObject(
            CustomerLoggedIn::class,
            [
                'weeeHelper' => $this->weeeHelperMock,
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

        $this->weeeHelperMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);

        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* @var \Magento\Customer\Api\Data\AddressInterface|MockObject $address */
        $address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);

        $this->observerMock->expects($this->once())
            ->method('getData')
            ->with('customer')
            ->willReturn($customerMock);

        $this->addressManagerMock->expects($this->once())
            ->method('setDefaultAddressAfterLogIn')
            ->with([$address]);

        $this->session->execute($this->observerMock);
    }
}
