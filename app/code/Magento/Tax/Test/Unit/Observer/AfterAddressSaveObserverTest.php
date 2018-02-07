<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Helper\Data;

class AfterAddressSaveObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Observer
     */
    protected $observerMock;

    /**
     * @var Session
     */
    protected $customerSessionMock;

    /** @var ObjectManager */
    protected $objectManager;

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
     * @var AfterAddressSave
     */
    protected $session;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
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

        $this->taxHelperMock = $this->getMockBuilder('Magento\Tax\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->session = $this->objectManager->getObject(
            'Magento\Tax\Observer\AfterAddressSaveObserver',
            [
                'customerSession' => $this->customerSessionMock,
                'taxHelper' => $this->taxHelperMock,
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

        $this->taxHelperMock->expects($this->any())
            ->method('isCatalogPriceDisplayAffectedByTax')
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
