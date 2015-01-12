<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer\Backend;

class CustomerQuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Observer\Backend\CustomerQuote
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\\Magento\Sales\Model\QuoteRepository
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
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('Magento\Customer\Model\Config\Share')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->getMockBuilder('\Magento\Sales\Model\QuoteRepository')
            ->disableOriginalConstructor()
            ->setMethods(['getForCustomer', 'save'])
            ->getMock();
        $this->observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getOrigCustomerDataObject', 'getCustomerDataObject'])
            ->getMock();
        $this->observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->eventMock));
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->customerQuote = $objectManager->getObject(
            'Magento\Sales\Model\Observer\Backend\CustomerQuote',
            [
                'storeManager' => $this->storeManagerMock,
                'config' => $this->configMock,
                'quoteRepository' => $this->quoteRepositoryMock,
            ]
        );
    }

    public function testDispatchNoCustomerGroupChange()
    {
        $customerDataObjectMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $customerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->will($this->returnValue(1));
        $origCustomerDataObjectMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
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
        $this->quoteRepositoryMock->expects($this->never())
            ->method('getForCustomer');

        $this->customerQuote->dispatch($this->observerMock);
    }

    /**
     * @param bool $isWebsiteScope
     * @param array $websites
     * @param int $quoteId
     * @dataProvider dispatchDataProvider
     */
    public function testDispatch($isWebsiteScope, $websites, $quoteId)
    {
        $this->configMock->expects($this->once())
            ->method('isWebsiteScope')
            ->will($this->returnValue($isWebsiteScope));
        $customerDataObjectMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
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
        $origCustomerDataObjectMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $origCustomerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->will($this->returnValue(2));
        $this->eventMock->expects($this->any())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customerDataObjectMock));
        $this->eventMock->expects($this->any())
            ->method('getOrigCustomerDataObject')
            ->will($this->returnValue($origCustomerDataObjectMock));
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote $quoteMock */
        $quoteMock = $this->getMockBuilder(
            'Magento\Sales\Model\Quote'
        )->setMethods(
                [
                    'setWebsite',
                    'setCustomerGroupId',
                    'collectTotals',
                    '__wakeup',
                ]
            )->disableOriginalConstructor(
            )->getMock();
        $websiteCount = count($websites);
        if ($quoteId) {
            $this->quoteRepositoryMock->expects($this->exactly($websiteCount))
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
        } else {
            $this->quoteRepositoryMock->expects($this->exactly($websiteCount))
                ->method('getForCustomer')
                ->willThrowException(
                    new \Magento\Framework\Exception\NoSuchEntityException()
                );
            $quoteMock->expects($this->never())
                ->method('setCustomerGroupId');
            $quoteMock->expects($this->never())
                ->method('collectTotals');
            $this->quoteRepositoryMock->expects($this->never())
                ->method('save');
        }
        $this->customerQuote->dispatch($this->observerMock);
    }

    public function dispatchDataProvider()
    {
        return [
            [true, ['website1'], 3],
            [true, ['website1', 'website2'], 3],
            [false, ['website1'], 3],
            [false, ['website1', 'website2'], 3],
            [true, ['website1'], null],
            [true, ['website1', 'website2'], null],
            [false, ['website1'], null],
            [false, ['website1', 'website2'], null],
        ];
    }
}
