<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
            ->getMock();
        $this->configMock = $this->getMockBuilder(Share::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerDataObject', 'getOrigCustomerDataObject'])
            ->getMock();
        $this->observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->eventMock));
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
            ->getMock();
        $customerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->will($this->returnValue(1));
        $origCustomerDataObjectMock = $this->getMockBuilder(CustomerInterface::class)
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
            ->will($this->returnValue($isWebsiteScope));
        $customerDataObjectMock = $this->getMockBuilder(CustomerInterface::class)
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
        )->disableOriginalConstructor()->getMock();
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
