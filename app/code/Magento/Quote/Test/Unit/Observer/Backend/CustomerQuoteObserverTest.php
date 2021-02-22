<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Observer\Backend;

class CustomerQuoteObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Observer\Backend\CustomerQuoteObserver
     */
    protected $customerQuote;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Customer\Model\Config\Share
     */
    protected $configMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event\Observer
     */
    protected $observerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event
     */
    protected $eventMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(\Magento\Customer\Model\Config\Share::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerDataObject', 'getOrigCustomerDataObject'])
            ->getMock();
        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($this->eventMock);
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
            ->willReturn(1);
        $origCustomerDataObjectMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $origCustomerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->willReturn(1);
        $this->eventMock->expects($this->any())
            ->method('getCustomerDataObject')
            ->willReturn($customerDataObjectMock);
        $this->eventMock->expects($this->any())
            ->method('getOrigCustomerDataObject')
            ->willReturn($origCustomerDataObjectMock);
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
            ->willReturn($isWebsiteScope);
        $customerDataObjectMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->willReturn(1);
        $customerDataObjectMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(2);
        if ($isWebsiteScope) {
            $websites = $websites[0];
            $this->storeManagerMock->expects($this->once())
                ->method('getWebsite')
                ->with(2)
                ->willReturn($websites);
        } else {
            $this->storeManagerMock->expects($this->once())
                ->method('getWebsites')
                ->willReturn($websites);
        }
        $this->eventMock->expects($this->any())
            ->method('getCustomerDataObject')
            ->willReturn($customerDataObjectMock);
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Quote\Model\Quote $quoteMock */
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
        )->disableOriginalConstructor()->getMock();
        $websiteCount = count($websites);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getForCustomer')
            ->willReturn($quoteMock);
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

    /**
     * @return array
     */
    public function dispatchDataProvider()
    {
        return [
            [true, [['website1']]],
            [true, [['website1'], ['website2']]],
            [false, ['website1']],
            [false, ['website1', 'website2']],
        ];
    }
}
