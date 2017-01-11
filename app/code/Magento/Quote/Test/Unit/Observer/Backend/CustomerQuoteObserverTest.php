<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Test\Unit\Observer\Backend;

class CustomerQuoteObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Observer\Backend\CustomerQuoteObserver
     */
    protected $customerQuote;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Config\Share
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event\Observer
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event
     */
    protected $eventMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(\Magento\Customer\Model\Config\Share::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerDataObject'])
            ->getMock();
        $this->observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->eventMock));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerQuote = $objectManager->getObject(
            \Magento\Quote\Observer\Backend\CustomerQuoteObserver::class,
            [
                'storeManager' => $this->storeManagerMock,
                'config' => $this->configMock,
                'quoteRepository' => $this->quoteRepositoryMock,
            ]
        );
    }

    public function testDispatchNoCustomerGroupChange()
    {
        $customerDataObjectMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->will($this->returnValue(1));
        $origCustomerDataObjectMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $origCustomerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->will($this->returnValue(1));
        $this->eventMock->expects($this->any())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customerDataObjectMock));
        $this->eventMock->expects($this->any())
            ->method('getOrigCustomerDataObject')
            ->will($this->returnValue($origCustomerDataObjectMock));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getForCustomer')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());

        $this->customerQuote->execute($this->observerMock);
    }

    /**
     * @param bool $isWebsiteScope
     * @param array $websites
     * @dataProvider dispatchDataProvider
     */
    public function testDispatch($isWebsiteScope, $websites)
    {
        $this->configMock->expects($this->once())
            ->method('isWebsiteScope')
            ->will($this->returnValue($isWebsiteScope));
        $customerDataObjectMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->will($this->returnValue(1));
        $customerDataObjectMock->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(2));
        if ($isWebsiteScope) {
            $websites = $websites[0];
            $this->storeManagerMock->expects($this->once())
                ->method('getWebsite')
                ->with(2)
                ->will($this->returnValue($websites));
        } else {
            $this->storeManagerMock->expects($this->once())
                ->method('getWebsites')
                ->will($this->returnValue($websites));
        }
        $this->eventMock->expects($this->any())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customerDataObjectMock));
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Quote\Model\Quote $quoteMock */
        $quoteMock = $this->getMockBuilder(
            \Magento\Quote\Model\Quote::class
        )->setMethods(
                [
                    'setWebsite',
                    'setCustomerGroupId',
                    'getCustomerGroupId',
                    'collectTotals',
                    '__wakeup',
                ]
            )->disableOriginalConstructor(
            )->getMock();
        $websiteCount = count($websites);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getForCustomer')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->exactly($websiteCount))
            ->method('setWebsite');
        $quoteMock->expects($this->exactly($websiteCount))
            ->method('setCustomerGroupId');
        $quoteMock->expects($this->exactly($websiteCount))
            ->method('collectTotals');
        $this->quoteRepositoryMock->expects($this->exactly($websiteCount))
            ->method('save')
            ->with($quoteMock);
        $quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn(2);
        $this->customerQuote->execute($this->observerMock);
    }

    public function dispatchDataProvider()
    {
        return [
            [true, ['website1']],
            [true, ['website1', 'website2']],
            [false, ['website1']],
            [false, ['website1', 'website2']],
        ];
    }
}
