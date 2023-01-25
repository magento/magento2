<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Observer\Backend;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Observer\Backend\CustomerQuoteObserver;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerQuoteObserverTest extends TestCase
{
    /**
     * @var CustomerQuoteObserver
     */
    protected $customerQuote;

    /**
     * @var MockObject|StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var MockObject|Share
     */
    protected $configMock;

    /**
     * @var MockObject|CartRepositoryInterface
     */
    protected $quoteRepositoryMock;

    /**
     * @var MockObject|Observer
     */
    protected $observerMock;

    /**
     * @var MockObject|Event
     */
    protected $eventMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(Share::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerDataObject', 'getOrigCustomerDataObject'])
            ->getMock();
        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($this->eventMock);
        $objectManager = new ObjectManager($this);
        $this->customerQuote = $objectManager->getObject(
            CustomerQuoteObserver::class,
            [
                'storeManager' => $this->storeManagerMock,
                'config' => $this->configMock,
                'quoteRepository' => $this->quoteRepositoryMock,
            ]
        );
    }

    public function testDispatchNoCustomerGroupChange()
    {
        $customerDataObjectMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->willReturn(1);
        $origCustomerDataObjectMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
            ->willThrowException(new NoSuchEntityException());

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
        $customerDataObjectMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
        /** @var MockObject|Quote $quoteMock */
        $quoteMock = $this->getMockBuilder(
            Quote::class
        )->setMethods(
            [
                'setWebsite',
                'setCustomerGroupId',
                'getCustomerGroupId',
                'collectTotals',
                '__wakeup',
            ]
        )->disableOriginalConstructor()
            ->getMock();
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
