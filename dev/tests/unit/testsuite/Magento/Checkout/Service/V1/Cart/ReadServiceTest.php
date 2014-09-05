<?php
/** 
 * 
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

namespace Magento\Checkout\Service\V1\Cart;

use \Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemTotalBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemTotalMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', ['get'], [], '', false);
        $methods = [
            'getId', 'getStoreId', 'getCreatedAt', 'getUpdatedAt', 'getConvertedAt',
            'getIsActive', 'getIsVirtual', 'getItemsCount', 'getItemsQty', 'getCheckoutMethod', 'getReservedOrderId',
            'getOrigOrderId', 'getBaseGrandTotal', 'getBaseSubtotal', 'getSubtotal', 'getBaseSubtotalWithDiscount',
            'getSubtotalWithDiscount', 'getCustomerId', 'getCustomerEmail', 'getCustomerGroupId',
            'getCustomerTaxClassId', 'getCustomerPrefix', 'getCustomerFirstname', 'getCustomerMiddlename',
            'getCustomerLastname', 'getCustomerSuffix', 'getCustomerDob', 'getCustomerNote', 'getCustomerNoteNotify',
            'getCustomerIsGuest', 'getCustomerGender', 'getCustomerTaxvat', '__wakeup', 'load', 'getGrandTotal',
            'getGlobalCurrencyCode', 'getBaseCurrencyCode', 'getStoreCurrencyCode', 'getQuoteCurrencyCode',
            'getStoreToBaseRate', 'getStoreToQuoteRate', 'getBaseToGlobalRate', 'getBaseToQuoteRate', 'setStoreId',
            'getShippingAddress', 'getAllItems'
        ];
        $this->quoteMock = $this->getMock('\Magento\Sales\Model\Quote', $methods, [], '', false);
        $this->quoteCollectionMock = $objectManager->getCollectionMock(
            '\Magento\Sales\Model\Resource\Quote\Collection', [$this->quoteMock]);
        $this->searchResultsBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\CartSearchResultsBuilder', [], [], '', false);

        $this->cartBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\CartBuilder', [], [], '', false);
        $this->cartMapperMock = $this->getMock('\Magento\Checkout\Service\V1\Data\CartMapper', ['map']);

        $this->totalsBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\TotalsBuilder', [], [], '', false);
        $this->totalsMapperMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\TotalsMapper', ['map']);
        $this->totalsMock = $this->getMock('Magento\Sales\Model\Order\Total', [], [], '', false);

        $this->customerBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\CustomerBuilder', [], [], '', false);
        $this->customerMapperMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\CustomerMapper', ['map']);
        $this->customerMock = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);

        $this->currencyBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\CurrencyBuilder', [], [], '', false);
        $this->currencyMapperMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\CurrencyMapper', ['extractDto']);

        $this->itemTotalBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\Totals\ItemBuilder', [], [], '', false);
        $this->itemTotalMapperMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\Totals\ItemMapper', ['extractDto']);

        $this->currencyMock = $this->getMock('Magento\Checkout\Service\V1\Data\Cart\Currency', [], [], '', false);

        $this->service = new ReadService(
            $this->quoteRepositoryMock,
            $this->quoteCollectionMock,
            $this->searchResultsBuilderMock,
            $this->cartBuilderMock,
            $this->cartMapperMock,
            $this->totalsBuilderMock,
            $this->totalsMapperMock,
            $this->customerBuilderMock,
            $this->customerMapperMock,
            $this->currencyBuilderMock,
            $this->currencyMapperMock,
            $this->itemTotalBuilderMock,
            $this->itemTotalMapperMock
        );
    }

    public function testGetCart()
    {
        $cartId = 12;
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)
            ->will($this->returnValue($this->quoteMock));

        $this->cartBuilderMock->expects($this->once())->method('setCustomer')->with($this->customerMock);
        $this->cartBuilderMock->expects($this->once())->method('setTotals')->with($this->totalsMock);
        $this->cartBuilderMock->expects($this->once())->method('setCurrency')->with($this->currencyMock);
        $this->cartBuilderMock->expects($this->once())->method('create');

        $this->setCartTotalsExpectations();
        $this->setCartDataExpectations();
        $this->setCurrencyDataExpectations();
        $this->setCustomerDataExpectations();
        $this->setCartItemTotalsExpectations();

        $this->service->getCart($cartId);
    }

    /**
     * @param int $direction
     * @param string $expected
     * @dataProvider getCartListSuccessDataProvider
     */
    public function testGetCartListSuccess($direction, $expected)
    {
        $searchResult = $this->getMock('\Magento\Checkout\Service\V1\Data\CartSearchResults', [], [], '', false);
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Service\V1\Data\SearchCriteria', [], [], '', false);

        $cartMock = $this->getMock('Magento\Payment\Model\Cart', [], [], '', false);
        $this->searchResultsBuilderMock
            ->expects($this->once())
            ->method('setSearchCriteria')
            ->will($this->returnValue($searchCriteriaMock));
        $filterGroupMock = $this->getMock('\Magento\Framework\Service\V1\Data\Search\FilterGroup', [], [], '', false);
        $searchCriteriaMock
            ->expects($this->any())
            ->method('getFilterGroups')
            ->will($this->returnValue([$filterGroupMock]));
        $filterMock = $this->getMock('\Magento\Framework\Service\V1\Data\Filter', [], [], '', false);
        $filterGroupMock->expects($this->any())->method('getFilters')->will($this->returnValue([$filterMock]));
        $filterMock->expects($this->once())->method('getField')->will($this->returnValue('store_id'));
        $filterMock->expects($this->any())->method('getConditionType')->will($this->returnValue('eq'));
        $filterMock->expects($this->once())->method('getValue')->will($this->returnValue('filter_value'));
        $this->quoteCollectionMock
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with(['store_id'], [0 => ['eq' => 'filter_value']]);

        $this->quoteCollectionMock->expects($this->once())->method('getSize')->will($this->returnValue(10));
        $this->searchResultsBuilderMock->expects($this->once())->method('setTotalCount')->with(10);

        $searchCriteriaMock
            ->expects($this->once())
            ->method('getSortOrders')
            ->will($this->returnValue(['id' => $direction]));
        $this->quoteCollectionMock->expects($this->once())->method('addOrder')->with('entity_id', $expected);
        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->will($this->returnValue(1));
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->will($this->returnValue(10));

        $this->setCartTotalsExpectations();
        $this->setCartDataExpectations();
        $this->setCustomerDataExpectations();
        $this->setCurrencyDataExpectations();
        $this->setCartItemTotalsExpectations();

        $this->cartBuilderMock->expects($this->once())->method('setCurrency')->with($this->currencyMock);
        $this->cartBuilderMock->expects($this->once())->method('setCustomer')->with($this->customerMock);
        $this->cartBuilderMock->expects($this->once())->method('setTotals')->with($this->totalsMock);
        $this->cartBuilderMock->expects($this->once())->method('create')->will($this->returnValue($cartMock));

        $this->searchResultsBuilderMock->expects($this->once())->method('setItems')->with([$cartMock]);
        $this->searchResultsBuilderMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchResult));
        $this->assertEquals($searchResult, $this->service->getCartList($searchCriteriaMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Field 'any_value' cannot be used for search.
     */
    public function testGetCartListWithNotExistingField()
    {
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Service\V1\Data\SearchCriteria', [], [], '', false);
        $this->searchResultsBuilderMock
            ->expects($this->once())
            ->method('setSearchCriteria')
            ->will($this->returnValue($searchCriteriaMock));

        $filterGroupMock = $this->getMock('\Magento\Framework\Service\V1\Data\Search\FilterGroup', [], [], '', false);
        $searchCriteriaMock
            ->expects($this->any())
            ->method('getFilterGroups')
            ->will($this->returnValue([$filterGroupMock]));
        $filterMock = $this->getMock('\Magento\Framework\Service\V1\Data\Filter', [], [], '', false);
        $filterGroupMock->expects($this->any())->method('getFilters')->will($this->returnValue([$filterMock]));
        $filterMock->expects($this->once())->method('getField')->will($this->returnValue('any_value'));
        $filterMock->expects($this->never())->method('getConditionType');
        $this->service->getCartList($searchCriteriaMock);
    }

    /**
     * @return array
     */
    public function getCartListSuccessDataProvider()
    {
        return [
            'asc' => [SearchCriteria::SORT_ASC, 'ASC'],
            'desc' => [SearchCriteria::SORT_DESC, 'DESC']
        ];
    }

    /**
     * Set expectations for cart general data processing
     */
    protected function setCartDataExpectations()
    {
        $this->cartMapperMock->expects($this->once())->method('map')->with($this->quoteMock)
            ->will($this->returnValue([]));
        $this->cartBuilderMock->expects($this->once())->method('populateWithArray')->with([]);
    }

    /**
     * Set expectations for totals processing
     */
    protected function setCartTotalsExpectations()
    {
        $this->totalsMapperMock->expects($this->once())->method('map')->with($this->quoteMock)
            ->will($this->returnValue([]));
        $this->totalsBuilderMock->expects($this->once())->method('populateWithArray')->with([]);
        $this->totalsBuilderMock->expects($this->once())->method('create')->will($this->returnValue($this->totalsMock));
    }

    /**
     * Set expectations for totals item data processing
     *
     * @return array
     */
    protected function setCartItemTotalsExpectations()
    {
        $quoteItemMock = $this->getMock('\Magento\Sales\Model\Quote\Item', [], [], '', false);
        $items = [$quoteItemMock];
        $this->quoteMock->expects($this->once())->method('getAllItems')->will($this->returnValue($items));
        $this->itemTotalMapperMock->expects($this->once())->method('extractDto')->with($quoteItemMock);
    }

    /**
     * Set expectations for cart customer data processing
     */
    protected function setCustomerDataExpectations()
    {
        $this->customerMapperMock->expects($this->once())->method('map')->with($this->quoteMock)
            ->will($this->returnValue([]));
        $this->customerBuilderMock->expects($this->once())->method('populateWithArray')->with([]);
        $this->customerBuilderMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->customerMock));
    }

    /**
     * Set expectations for currency data processing
     */
    protected function setCurrencyDataExpectations()
    {
        $this->currencyMapperMock->expects($this->once())->method('extractDto')->with($this->quoteMock)
            ->will($this->returnValue($this->currencyMock));
    }

    public function testGetTotals()
    {
        $cartId = 12;
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)
            ->will($this->returnValue($this->quoteMock));

        $this->setCartTotalsExpectations();
        $this->setCartItemTotalsExpectations();

        $this->service->getTotals($cartId);
    }
}
