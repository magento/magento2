<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Observer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\PageCache\Model\Config;
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Weee\Helper\Data;
use Magento\Weee\Observer\CustomerLoggedIn;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests to cover CustomerLoggedIn
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerLoggedInTest extends TestCase
{
    use MockCreationTrait;

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
        $this->observerMock = $this->createPartialMockWithReflection(
            Observer::class,
            ['getData', 'getCustomerAddress']
        );

        $this->moduleManagerMock = $this->createMock(Manager::class);

        $this->cacheConfigMock = $this->createMock(Config::class);

        $this->weeeHelperMock = $this->createMock(Data::class);

        $this->addressManagerMock = $this->createPartialMock(
            TaxAddressManagerInterface::class,
            ['setDefaultAddressAfterSave', 'setDefaultAddressAfterLogIn']
        );

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

        $customerMock = $this->createMock(Customer::class);

        /** @var AddressInterface|MockObject $address */
        $address = $this->createMock(AddressInterface::class);

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
