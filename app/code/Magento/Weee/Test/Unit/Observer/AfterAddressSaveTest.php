<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Observer;

class AfterAddressSaveTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Weee\Observer\AfterAddressSave
     */
    protected $session;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress', 'getData'
            ])
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([
                'setDefaultTaxBillingAddress', 'setDefaultTaxShippingAddress', 'setWebsiteId'
            ])
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder('Magento\Framework\Module\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder('Magento\PageCache\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeHelperMock = $this->getMockBuilder('Magento\Weee\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->session = $this->objectManager->getObject(
            'Magento\Weee\Observer\AfterAddressSave',
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

        $address = $this->objectManager->getObject('Magento\Customer\Model\Address');
        $address->setIsDefaultShipping(true);
        $address->setIsDefaultBilling(true);
        $address->setIsPrimaryBilling(true);
        $address->setIsPrimaryShipping(true);
        $address->setCountryId(1);
        $address->setData('postcode', 11111);

        $this->customerSessionMock->expects($this->once())
            ->method('setDefaultTaxBillingAddress')
            ->with(['country_id' => 1, 'region_id' => null, 'postcode' => 11111]);
        $this->customerSessionMock->expects($this->once())
            ->method('setDefaultTaxShippingAddress')
            ->with(['country_id' => 1, 'region_id' => null, 'postcode' => 11111]);

        $this->observerMock->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->session->execute($this->observerMock);
    }
}
