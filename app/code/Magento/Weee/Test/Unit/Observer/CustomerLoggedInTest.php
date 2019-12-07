<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Observer;

use Magento\Tax\Api\TaxAddressManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Customer logged in test
 */
class CustomerLoggedInTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $observerMock;

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
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelperMock;

    /**
     * @var TaxAddressManagerInterface|MockObject
     */
    private $addressManagerMock;

    /**
     * @var \Magento\Weee\Observer\CustomerLoggedIn
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

        $this->moduleManagerMock = $this->getMockBuilder(\Magento\Framework\Module\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder(\Magento\PageCache\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeHelperMock = $this->getMockBuilder(\Magento\Weee\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressManagerMock = $this->getMockBuilder(TaxAddressManagerInterface::class)
            ->setMethods(['setDefaultAddressAfterSave', 'setDefaultAddressAfterLogIn'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->session = $objectManager->getObject(
            \Magento\Weee\Observer\CustomerLoggedIn::class,
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

        $customerMock = $this->getMockBuilder(\Magento\Customer\Model\Data\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* @var \Magento\Customer\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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
