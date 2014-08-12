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

use \Magento\Checkout\Service\V1\Data\Cart;
use \Magento\Checkout\Service\V1\Data\Cart\Totals;
use \Magento\Checkout\Service\V1\Data\Cart\Customer;
use \Magento\Framework\Service\V1\Data\SearchCriteria;
use \Magento\Checkout\Service\V1\Data\Cart\Currency;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->quoteFactoryMock =
            $this->getMock('\Magento\Sales\Model\QuoteFactory', ['create'], [], '', false);
        $methods = [
            'getId', 'getStoreId', 'getCreatedAt', 'getUpdatedAt', 'getConvertedAt',
            'getIsActive', 'getIsVirtual', 'getItemsCount', 'getItemsQty', 'getCheckoutMethod', 'getReservedOrderId',
            'getOrigOrderId', 'getBaseGrandTotal', 'getBaseSubtotal', 'getSubtotal', 'getBaseSubtotalWithDiscount',
            'getSubtotalWithDiscount', 'getCustomerId', 'getCustomerEmail', 'getCustomerGroupId',
            'getCustomerTaxClassId', 'getCustomerPrefix', 'getCustomerFirstname', 'getCustomerMiddlename',
            'getCustomerLastname', 'getCustomerSuffix', 'getCustomerDob', 'getCustomerNote', 'getCustomerNoteNotify',
            'getCustomerIsGuest', 'getCustomerGender', 'getCustomerTaxvat', '__wakeup', 'load', 'getGrandTotal',
            'getGlobalCurrencyCode', 'getBaseCurrencyCode', 'getStoreCurrencyCode', 'getQuoteCurrencyCode',
            'getStoreToBaseRate', 'getStoreToQuoteRate', 'getBaseToGlobalRate', 'getBaseToQuoteRate',
        ];
        $this->quoteMock = $this->getMock('\Magento\Sales\Model\Quote', $methods, [], '', false);
        $this->quoteCollectionMock = $objectManager->getCollectionMock(
            '\Magento\Sales\Model\Resource\Quote\Collection', [$this->quoteMock]);
        $this->cartBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\CartBuilder', [], [], '', false);
        $this->searchResultsBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\CartSearchResultsBuilder', [], [], '', false);
        $this->totalsBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\TotalsBuilder', [], [], '', false);
        $this->customerBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\CustomerBuilder', [], [], '', false);
        $this->currencyBuilderMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\CurrencyBuilder', [], [], '', false);

        $this->service = new ReadService(
            $this->quoteFactoryMock,
            $this->quoteCollectionMock,
            $this->cartBuilderMock,
            $this->searchResultsBuilderMock,
            $this->totalsBuilderMock,
            $this->customerBuilderMock,
            $this->currencyBuilderMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no cart with provided ID.
     */
    public function testGetCartWithNoSuchEntityException()
    {
        $cartId = 12;
        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('load')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue(13));
        $this->cartBuilderMock->expects($this->never())->method('populateWithArray');

        $this->service->getCart($cartId);
    }

    public function testGetCartSuccess()
    {
        $cartId = 12;
        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('load')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->any())->method('getId')->will($this->returnValue($cartId));
        $this->cartBuilderMock->expects($this->once())->method('populateWithArray');
        $this->totalsBuilderMock->expects($this->once())->method('populateWithArray');
        $this->customerBuilderMock->expects($this->once())->method('populateWithArray');
        $this->currencyBuilderMock->expects($this->once())->method('populateWithArray');
        $this->cartBuilderMock->expects($this->once())->method('setCustomer');
        $this->cartBuilderMock->expects($this->once())->method('setTotals');
        $this->cartBuilderMock->expects($this->once())->method('setCurrency');
        $this->cartBuilderMock->expects($this->once())->method('create');

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
        $customerMock = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);
        $totalMock = $this->getMock('Magento\Sales\Model\Order\Total', [], [], '', false);
        $cartMock = $this->getMock('Magento\Payment\Model\Cart', [], [], '', false);
        $currencyMock = $this->getMock('Magento\Checkout\Service\V1\Data\Cart\Currency', [], [], '', false);
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
        $this->getTotalData();
        $this->getCartData();
        $this->getCustomerData();
        $this->setCurrencyDataExpectations();
        $this->currencyBuilderMock->expects($this->once())->method('create')->will($this->returnValue($currencyMock));
        $this->cartBuilderMock->expects($this->once())->method('setCurrency')->with($currencyMock);

        $this->customerBuilderMock->expects($this->once())->method('create')->will($this->returnValue($customerMock));
        $this->cartBuilderMock->expects($this->once())->method('setCustomer')->with($customerMock);
        $this->totalsBuilderMock->expects($this->once())->method('create')->will($this->returnValue($totalMock));
        $this->cartBuilderMock->expects($this->once())->method('setTotals')->will($this->returnValue($totalMock));
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

    public function getCartListSuccessDataProvider()
    {
        return [
            'asc' => [SearchCriteria::SORT_ASC, 'ASC'],
            'desc' => [SearchCriteria::SORT_DESC, 'DESC']
        ];
    }

    protected function getCartData()
    {
        $expected = [
            Cart::ID => 10,
            Cart::STORE_ID => 1,
            Cart::CREATED_AT => '2014-04-02 12:28:50',
            Cart::UPDATED_AT => '2014-04-02 12:28:50',
            Cart::CONVERTED_AT => '2014-04-02 12:28:50',
            Cart::IS_ACTIVE => true,
            Cart::IS_VIRTUAL => false,
            Cart::ITEMS_COUNT => 10,
            Cart::ITEMS_QUANTITY => 15,
            Cart::CHECKOUT_METHOD => 'check mo',
            Cart::RESERVED_ORDER_ID => 'order_id',
            Cart::ORIG_ORDER_ID => 'orig_order_id'
        ];
        $expectedMethods = [
            'getId' => 10,
            'getStoreId' => 1,
            'getCreatedAt' => '2014-04-02 12:28:50',
            'getUpdatedAt' => '2014-04-02 12:28:50',
            'getConvertedAt' => '2014-04-02 12:28:50',
            'getIsActive' => true,
            'getIsVirtual' => false,
            'getItemsCount' => 10,
            'getItemsQty' => 15,
            'getCheckoutMethod' => 'check mo',
            'getReservedOrderId' => 'order_id',
            'getOrigOrderId' => 'orig_order_id'
        ];
        foreach ($expectedMethods as $method => $value) {
            $this->quoteMock->expects($this->once())->method($method)->will($this->returnValue($value));
        }
        $this->cartBuilderMock->expects($this->once())->method('populateWithArray')->with($expected);
    }

    protected function getTotalData()
    {
        $expected = [
            Totals::BASE_GRAND_TOTAL => 100,
            Totals::GRAND_TOTAL => 150,
            Totals::BASE_SUBTOTAL => 150,
            Totals::SUBTOTAL => 150,
            Totals::BASE_SUBTOTAL_WITH_DISCOUNT => 120,
            Totals::SUBTOTAL_WITH_DISCOUNT => 120,
        ];
        $expectedMethods = [
            'getBaseGrandTotal' => 100,
            'getGrandTotal' => 150,
            'getBaseSubtotal' => 150,
            'getSubtotal' => 150,
            'getBaseSubtotalWithDiscount' => 120,
            'getSubtotalWithDiscount' => 120
        ];
        foreach ($expectedMethods as $method => $value) {
            $this->quoteMock->expects($this->once())->method($method)->will($this->returnValue($value));
        }
        $this->totalsBuilderMock->expects($this->once())->method('populateWithArray')->with($expected);
    }

    protected function getCustomerData()
    {
        $expected = [
            Customer::ID => 10,
            Customer::EMAIL => 'customer@example.com',
            Customer::GROUP_ID => '4',
            Customer::TAX_CLASS_ID => 10,
            Customer::PREFIX => 'prefix_',
            Customer::FIRST_NAME => 'First Name',
            Customer::MIDDLE_NAME => 'Middle Name',
            Customer::LAST_NAME => 'Last Name',
            Customer::SUFFIX => 'suffix',
            Customer::DOB => '1/1/1989',
            Customer::NOTE => 'customer_note',
            Customer::NOTE_NOTIFY => 'note_notify',
            Customer::IS_GUEST => false,
            Customer::GENDER => 'male',
            Customer::TAXVAT => 'taxvat',
            ];
        $expectedMethods = [
            'getCustomerId' => 10,
            'getCustomerEmail' => 'customer@example.com',
            'getCustomerGroupId' => 4,
            'getCustomerTaxClassId' => 10,
            'getCustomerPrefix' => 'prefix_',
            'getCustomerFirstname' => 'First Name',
            'getCustomerMiddlename' => 'Middle Name',
            'getCustomerLastname' => 'Last Name',
            'getCustomerSuffix' => 'suffix',
            'getCustomerDob' => '1/1/1989',
            'getCustomerNote' => 'customer_note',
            'getCustomerNoteNotify' => 'note_notify',
            'getCustomerIsGuest' => false,
            'getCustomerGender' => 'male',
            'getCustomerTaxvat' => 'taxvat',
        ];
        foreach ($expectedMethods as $method => $value) {
            $this->quoteMock->expects($this->once())->method($method)->will($this->returnValue($value));
        }
        $this->customerBuilderMock->expects($this->once())->method('populateWithArray')->with($expected);
    }

    protected function setCurrencyDataExpectations()
    {
        $expected = [
            Currency::GLOBAL_CURRENCY_CODE => 'USD',
            Currency::BASE_CURRENCY_CODE => 'EUR',
            Currency::STORE_CURRENCY_CODE => 'USD',
            Currency::QUOTE_CURRENCY_CODE => 'EUR',
            Currency::STORE_TO_BASE_RATE => 1,
            Currency::STORE_TO_QUOTE_RATE => 2,
            Currency::BASE_TO_GLOBAL_RATE => 3,
            Currency::BASE_TO_QUOTE_RATE => 4,
        ];
        $expectedMethods = [
            'getGlobalCurrencyCode' => 'USD',
            'getBaseCurrencyCode' => 'EUR',
            'getStoreCurrencyCode' => 'USD',
            'getQuoteCurrencyCode' => 'EUR',
            'getStoreToBaseRate' => 1,
            'getStoreToQuoteRate' => 2,
            'getBaseToGlobalRate' => 3,
            'getBaseToQuoteRate' => 4,
        ];
        foreach ($expectedMethods as $method => $value) {
            $this->quoteMock->expects($this->once())->method($method)->will($this->returnValue($value));
        }
        $this->currencyBuilderMock->expects($this->once())->method('populateWithArray')->with($expected);
    }
}
