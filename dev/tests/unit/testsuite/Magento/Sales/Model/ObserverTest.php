<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model;

use Magento\Framework\Object;
use Magento\TestFramework\Helper\ObjectManager;
use Magento\Sales\Model\Quote\Address;

/**
 * Tests Magento\Sales\Model\Observer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Store\Model\StoresConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storesConfigMock;

    /**
     * @var \Magento\Sales\Model\Resource\Quote\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolverMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    /**
     * @var \Magento\Sales\Model\Resource\Report\OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \Magento\Sales\Model\Resource\Report\InvoicedFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceFactoryMock;

    /**
     * @var \Magento\Sales\Model\Resource\Report\RefundedFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $refundedFactoryMock;

    /**
     * @var \Magento\Sales\Model\Resource\Report\BestsellersFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bestsellersFactoryMock;

    /**
     * @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogDataMock;

    /**
     * @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressHelperMock;

    /**
     * @var \Magento\Sales\Model\Observer
     */
    protected $observer;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storesConfigMock = $this->getMockBuilder('Magento\Store\Model\StoresConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Quote\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->localeResolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->invoiceFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\InvoicedFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->refundedFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\RefundedFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->bestsellersFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\BestsellersFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->catalogDataMock = $this->getMockBuilder('Magento\Catalog\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAddressHelperMock = $this->getMockBuilder('Magento\Customer\Helper\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $this->observer = (new ObjectManager($this))
            ->getObject(
                'Magento\Sales\Model\Observer',
                [
                    'eventManager' => $this->eventManagerMock,
                    'storesConfig' => $this->storesConfigMock,
                    'quoteFactory' => $this->quoteFactoryMock,
                    'localeDate' => $this->localeDateMock,
                    'localeResolver' => $this->localeResolverMock,
                    'orderFactory' => $this->orderFactoryMock,
                    'invoicedFactory' => $this->invoiceFactoryMock,
                    'refundedFactory' => $this->refundedFactoryMock,
                    'bestsellersFactory' => $this->bestsellersFactoryMock,
                    'catalogData' => $this->catalogDataMock,
                    'customerAddressHelper' => $this->customerAddressHelperMock,
                ]
            );
    }

    /**
     * @param array $lifetimes
     * @param array $additionalFilterFields
     * @dataProvider cleanExpiredQuotesDataProvider
     */
    public function testCleanExpiredQuotes($lifetimes, $additionalFilterFields)
    {
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch');
        $this->storesConfigMock->expects($this->once())
            ->method('getStoresConfigByPath')
            ->will($this->returnValue($lifetimes));
        $quotesMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Quote\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteFactoryMock->expects($this->exactly(count($lifetimes)))
            ->method('create')
            ->will($this->returnValue($quotesMock));
        $quotesMock->expects($this->exactly((3 + count($additionalFilterFields)) * count($lifetimes)))
            ->method('addFieldToFilter');
        if (!empty($lifetimes)) {
            $quotesMock->expects($this->exactly(count($lifetimes)))
                ->method('walk')
                ->with('delete');
        }
        $schedule = (new ObjectManager($this))->getObject('Magento\Cron\Model\Schedule');
        $this->observer->setExpireQuotesAdditionalFilterFields($additionalFilterFields);
        $this->observer->cleanExpiredQuotes($schedule);
    }

    public function cleanExpiredQuotesDataProvider()
    {
        return [
            [[], []],
            [[1 => 100, 2 => 200], []],
            [[1 => 100, 2 => 200], ['field1' => 'condition1', 'field2' => 'condition2']],
        ];
    }

    public function testAggregateSalesReportOrderData()
    {
        $date = $this->setupAggregate();
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($orderMock));
        $schedule = (new ObjectManager($this))->getObject('Magento\Cron\Model\Schedule');
        $this->observer->aggregateSalesReportOrderData($schedule);
    }

    public function testAggregateSalesReportInvoicedData()
    {
        $date = $this->setupAggregate();
        $invoicedMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\Invoiced')
            ->disableOriginalConstructor()
            ->getMock();
        $invoicedMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->invoiceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($invoicedMock));
        $schedule = (new ObjectManager($this))->getObject('Magento\Cron\Model\Schedule');
        $this->observer->aggregateSalesReportInvoicedData($schedule);
    }

    public function testAggregateSalesReportRefundedData()
    {
        $date = $this->setupAggregate();
        $refundedMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\Refunded')
            ->disableOriginalConstructor()
            ->getMock();
        $refundedMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->refundedFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($refundedMock));
        $schedule = (new ObjectManager($this))->getObject('Magento\Cron\Model\Schedule');
        $this->observer->aggregateSalesReportRefundedData($schedule);
    }

    public function testAggregateSalesReportBestsellersData()
    {
        $date = $this->setupAggregate();
        $bestsellersMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\Bestsellers')
            ->disableOriginalConstructor()
            ->getMock();
        $bestsellersMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->bestsellersFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($bestsellersMock));
        $schedule = (new ObjectManager($this))->getObject('Magento\Cron\Model\Schedule');
        $this->observer->aggregateSalesReportBestsellersData($schedule);
    }
    /**
     * Set up aggregate
     *
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    protected function setupAggregate()
    {
        $date = (new ObjectManager($this))->getObject('Magento\Framework\Stdlib\DateTime\Date');
        $this->localeResolverMock->expects($this->once())
            ->method('emulate')
            ->with(0);
        $this->localeResolverMock->expects($this->once())
            ->method('revert');
        $dateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dateMock->expects($this->once())
            ->method('subHour')
            ->with(25)
            ->will($this->returnValue($date));
        $this->localeDateMock->expects($this->once())
            ->method('date')
            ->will($this->returnValue($dateMock));
        return $date;
    }

    /**
     * @param bool $isMsrpEnabled
     * @param bool $canApplyMsrp
     * @dataProvider setQuoteCanApplyMsrpDataProvider
     */
    public function testSetQuoteCanApplyMsrp($isMsrpEnabled, $canApplyMsrp)
    {
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote'])
            ->getMock();
        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'setCanApplyMsrp', 'getAllAddresses'])
            ->getMock();
        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($eventMock));
        $eventMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($quoteMock));
        $this->catalogDataMock->expects($this->once())
            ->method('isMsrpEnabled')
            ->will($this->returnValue($isMsrpEnabled));
        $quoteMock->expects($this->once())
            ->method('setCanApplyMsrp')
            ->with($canApplyMsrp);
        $addressMock1 = $this->getMockBuilder('Magento\Customer\Model\Address\AbstractAddress')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMockForAbstractClass();
        $addressMock1->setCanApplyMsrp($canApplyMsrp);
        $addressMock2 = $this->getMockBuilder('Magento\Customer\Model\Address\AbstractAddress')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMockForAbstractClass();
        $addressMock2->setCanApplyMsrp(false);
        $quoteMock->expects($this->any())
            ->method('getAllAddresses')
            ->will($this->returnValue([$addressMock1, $addressMock2]));
        $this->observer->setQuoteCanApplyMsrp($observerMock);
    }

    public function setQuoteCanApplyMsrpDataProvider()
    {
        return [
            [false, false],
            [true, true],
            [true, false]
        ];
    }

    /**
     * @param string $configAddressType
     * @param string|int $vatRequestId
     * @param string|int $vatRequestDate
     * @param string $orderHistoryComment
     * @dataProvider addVatRequestParamsOrderCommentDataProvider
     */
    public function testAddVatRequestParamsOrderComment(
        $configAddressType,
        $vatRequestId,
        $vatRequestDate,
        $orderHistoryComment
    ) {
        $this->customerAddressHelperMock->expects($this->once())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($configAddressType));
        $objectManager = new ObjectManager($this);
        $orderAddressMock = $objectManager->getObject('Magento\Sales\Model\Order\Address');
        $orderAddressMock->setVatRequestId($vatRequestId);
        $orderAddressMock->setVatRequestDate($vatRequestDate);
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->any())
            ->method('getShippingAddress')
            ->will($this->returnValue($orderAddressMock));
        if (is_null($orderHistoryComment)) {
            $orderMock->expects($this->never())
                ->method('addStatusHistoryComment');
        } else {
            $orderMock->expects($this->once())
                ->method('addStatusHistoryComment')
                ->with($orderHistoryComment, false);
        }
        $observer = $objectManager->getObject('Magento\Framework\Event\Observer');
        $observer->setOrder($orderMock);
        $this->observer->addVatRequestParamsOrderComment($observer);

    }

    public function addVatRequestParamsOrderCommentDataProvider()
    {
        return [
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING,
                'vatRequestId',
                'vatRequestDate',
                'VAT Request Identifier: vatRequestId<br />VAT Request Date: vatRequestDate',
            ],
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING,
                1,
                'vatRequestDate',
                null,
            ],
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING,
                'vatRequestId',
                1,
                null,
            ],
            [
                null,
                'vatRequestId',
                'vatRequestDate',
                null,
            ],
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_BILLING,
                'vatRequestId',
                'vatRequestDate',
                null,
            ],
        ];
    }
    /**
     * @param string $configAddressType
     * @param int $groupId
     * @param int $expectedPrevGroupId
     * @param int $expectedGroupId
     * @dataProvider restoreQuoteCustomerGroupIdDataProvider
     */
    public function testRestoreQuoteCustomerGroupId(
        $configAddressType,
        $groupId,
        $expectedPrevGroupId,
        $expectedGroupId
    ) {
        $quote = (new ObjectManager($this))->getObject('Magento\Sales\Model\Quote');
        $quoteAddress = (new ObjectManager($this))->getObject('Magento\Sales\Model\Quote\Address');
        $quoteAddress->setQuote($quote);
        if (!empty($groupId)) {
            $quoteAddress->setPrevQuoteCustomerGroupId($groupId);
        }
        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods(['getQuoteAddress'])
            ->getMock();
        $observerMock->expects($this->once())
            ->method('getQuoteAddress')
            ->will($this->returnValue($quoteAddress));
        $this->customerAddressHelperMock->expects($this->once())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($configAddressType));
        $this->observer->restoreQuoteCustomerGroupId($observerMock);
        $this->assertEquals($expectedGroupId, $quote->getCustomerGroupId());
        $this->assertEquals($expectedPrevGroupId, $quoteAddress->getPrevQuoteCustomerGroupId());
    }

    public function restoreQuoteCustomerGroupIdDataProvider()
    {
        return [
            [\Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING, 1, null, 1],
            [\Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING, null, null, null],
            [\Magento\Customer\Model\Address\AbstractAddress::TYPE_BILLING, 1, 1, null],
        ];
    }
}
