<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Observer;

class CustomerLoggedInTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Weee\Observer\CustomerLoggedIn
     */
    protected $session;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress', 'getData'
            ])
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setDefaultTaxBillingAddress', 'setDefaultTaxShippingAddress', 'setWebsiteId'
            ])
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

        $this->session = $this->objectManager->getObject(
            \Magento\Weee\Observer\CustomerLoggedIn::class,
            [
                'customerSession' => $this->customerSessionMock,
                'weeeHelper' => $this->weeeHelperMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock
            ]
        );
    }

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

        $this->observerMock->expects($this->once())
            ->method('getData')
            ->with('customer')
            ->willReturn($customerMock);

        $address = $this->objectManager->getObject(\Magento\Customer\Model\Data\Address::class);
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

        $this->session->execute($this->observerMock);
    }
}
