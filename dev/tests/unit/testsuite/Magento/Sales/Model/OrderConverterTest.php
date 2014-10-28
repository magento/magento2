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

/**
 * Class OrderConverterTest
 *
 * @package Magento\Sales\Model
 */
class OrderConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderBuilderMock;

    /**
     * @var \Magento\Sales\Model\Order\ItemConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemConverterMock;

    /**
     * @var \Magento\Sales\Model\Order\PaymentConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentConverterMock;

    /**
     * @var \Magento\Sales\Model\Order\AddressConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressConverterMock;

    /**
     * @var \Magento\Sales\Model\Order\Customer\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerBuilderMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\Order
     */
    protected $orderDataMock;

    /**
     * @var array
     */
    protected $orderData;

    /**
     * @var \Magento\Sales\Model\OrderConverter
     */
    protected $orderConverter;

    public function setUp()
    {
        $this->orderBuilderMock = $this->getMock(
            'Magento\Sales\Model\Order\Builder',
            [],
            [],
            '',
            false
        );
        $this->itemConverterMock = $this->getMock(
            'Magento\Sales\Model\Order\ItemConverter',
            ['getModel'],
            [],
            '',
            false
        );
        $this->paymentConverterMock = $this->getMock(
            'Magento\Sales\Model\Order\PaymentConverter',
            ['getModel'],
            [],
            '',
            false
        );
        $this->addressConverterMock = $this->getMock(
            'Magento\Sales\Model\Order\AddressConverter',
            ['getModel'],
            [],
            '',
            false
        );
        $this->customerBuilderMock = $this->getMock(
            'Magento\Sales\Model\Order\Customer\Builder',
            [],
            [],
            '',
            false
        );

        $this->orderDataMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\Order',
            [],
            [],
            '',
            false
        );

        $this->orderData = [
            'customerDob' => 'customer_dob',
            'customerEmail' => 'customer_email',
            'customerFirstName' => 'customer_first_name',
            'customerGender' => 'customer_gender',
            'customerGroupId' => 'customer_group_id',
            'customerId' => 'customer_id',
            'customerLastName' => 'customer_last_name',
            'customerMiddleName' => 'customer_middle_name',
            'customerIsGuest' => 'customer_is_guest',
            'customerNote' => 'customer_note',
            'customerNotify' => 'customer_notify',
            'customerPrefix' => 'customer_prefix',
            'customerSuffix' => 'customer_suffix',
            'customerTaxvat' => 'customer_taxvat',
            'items' => ['01' => 'item1', '02' => 'item2'],
            'payments' => ['payments'],
            'quoteId' => 'quote_id',
            'appliedRuleIds' => 'applied_rule_ids',
            'isVirtual' => 'is_virtual',
            'remoteIp' => 'remote_ip',
            'baseSubtotal' => 'base_subtotal',
            'subtotal' => 'ubtotal',
            'baseGrandTotal' => 'base_grand_total',
            'grandTotal' => 'grand_total',
            'baseCurrencyCode' => 'base_currency_code',
            'globalCurrencyCode' => 'global_rule_ids',
            'storeCurrencyCode' => 'store_rule_ids',
            'storeId' => 'store_id',
            'storeToBaseRate' => 'store_to_base_rate',
            'baseToGlobalRate' => 'base_to_global_rate',
            'couponCode' => 'coupon_code',
            'billingAddress' => 'billing_address',
            'shippingAddress' => 'shipping_address',
        ];
        $this->prepareDataObject();

        $this->orderConverter = new OrderConverter(
            $this->orderBuilderMock,
            $this->itemConverterMock,
            $this->paymentConverterMock,
            $this->addressConverterMock,
            $this->customerBuilderMock
        );
    }

    protected function prepareDataObject()
    {
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerDob')
            ->will($this->returnValue($this->orderData['customerDob']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerEmail')
            ->will($this->returnValue($this->orderData['customerEmail']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerFirstname')
            ->will($this->returnValue($this->orderData['customerFirstName']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerGender')
            ->will($this->returnValue($this->orderData['customerGender']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue($this->orderData['customerGroupId']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue($this->orderData['customerId']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue($this->orderData['customerIsGuest']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerLastname')
            ->will($this->returnValue($this->orderData['customerLastName']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerMiddlename')
            ->will($this->returnValue($this->orderData['customerMiddleName']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerNote')
            ->will($this->returnValue($this->orderData['customerNote']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerNoteNotify')
            ->will($this->returnValue($this->orderData['customerNotify']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerPrefix')
            ->will($this->returnValue($this->orderData['customerPrefix']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerSuffix')
            ->will($this->returnValue($this->orderData['customerSuffix']));
        $this->orderDataMock->expects($this->once())
            ->method('getCustomerTaxvat')
            ->will($this->returnValue($this->orderData['customerTaxvat']));
        $this->orderDataMock->expects($this->once())
            ->method('getQuoteId')
            ->will($this->returnValue($this->orderData['quoteId']));
        $this->orderDataMock->expects($this->once())
            ->method('getAppliedRuleIds')
            ->will($this->returnValue($this->orderData['appliedRuleIds']));
        $this->orderDataMock->expects($this->once())
            ->method('getIsVirtual')
            ->will($this->returnValue($this->orderData['isVirtual']));
        $this->orderDataMock->expects($this->once())
            ->method('getRemoteIp')
            ->will($this->returnValue($this->orderData['remoteIp']));
        $this->orderDataMock->expects($this->once())
            ->method('getBaseSubtotal')
            ->will($this->returnValue($this->orderData['baseSubtotal']));
        $this->orderDataMock->expects($this->once())
            ->method('getSubtotal')
            ->will($this->returnValue($this->orderData['subtotal']));
        $this->orderDataMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->will($this->returnValue($this->orderData['baseGrandTotal']));
        $this->orderDataMock->expects($this->once())
            ->method('getGrandTotal')
            ->will($this->returnValue($this->orderData['grandTotal']));
        $this->orderDataMock->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->will($this->returnValue($this->orderData['baseCurrencyCode']));
        $this->orderDataMock->expects($this->once())
            ->method('getGlobalCurrencyCode')
            ->will($this->returnValue($this->orderData['globalCurrencyCode']));
        $this->orderDataMock->expects($this->once())
            ->method('getStoreCurrencyCode')
            ->will($this->returnValue($this->orderData['storeCurrencyCode']));
        $this->orderDataMock->expects($this->once())
            ->method('getStoreId')
            ->will($this->returnValue($this->orderData['storeId']));
        $this->orderDataMock->expects($this->once())
            ->method('getStoreToBaseRate')
            ->will($this->returnValue($this->orderData['storeToBaseRate']));
        $this->orderDataMock->expects($this->once())
            ->method('getBaseToGlobalRate')
            ->will($this->returnValue($this->orderData['baseToGlobalRate']));
        $this->orderDataMock->expects($this->once())
            ->method('getCouponCode')
            ->will($this->returnValue($this->orderData['couponCode']));
        $this->orderDataMock->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($this->orderData['billingAddress']));
        $this->orderDataMock->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($this->orderData['shippingAddress']));

        return $this->orderDataMock;
    }

    protected function getCustomer()
    {
        $this->customerBuilderMock->expects($this->once())
            ->method('setDob')
            ->with($this->orderData['customerDob'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setEmail')
            ->with($this->orderData['customerEmail'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setFirstname')
            ->with($this->orderData['customerFirstName'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setGender')
            ->with($this->orderData['customerGender'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setGroupId')
            ->with($this->orderData['customerGroupId'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setId')
            ->with($this->orderData['customerId'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setIsGuest')
            ->with($this->orderData['customerIsGuest'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setLastname')
            ->with($this->orderData['customerLastName'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setMiddlename')
            ->with($this->orderData['customerMiddleName'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setNote')
            ->with($this->orderData['customerNote'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setNoteNotify')
            ->with($this->orderData['customerNotify'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setPrefix')
            ->with($this->orderData['customerPrefix'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setSuffix')
            ->with($this->orderData['customerSuffix'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->once())
            ->method('setTaxvat')
            ->with($this->orderData['customerTaxvat'])
            ->will($this->returnSelf());
        $customer = $this->getMock(
            'Magento\Sales\Model\Order\Customer',
            [],
            [],
            '',
            false
        );
        $this->customerBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($customer));

        return $customer;
    }

    protected function getItems()
    {
        $this->orderDataMock->expects($this->any())
            ->method('getItems')
            ->will($this->returnValue($this->orderData['items']));

        $orderItem1 = $this->getMock('Magento\Sales\Model\Order\Item',
            [],
            [],
            '',
            false
        );
        $orderItem2 = $this->getMock('Magento\Sales\Model\Order\Item',
            [],
            [],
            '',
            false
        );

        $this->itemConverterMock->expects($this->any())
            ->method('getModel')
            ->will($this->returnValueMap([
                        [$this->orderData['items']['01'], $orderItem1],
                        [$this->orderData['items']['02'], $orderItem2]
                    ]));

        return [$orderItem1, $orderItem2];
    }

    protected function getPayments()
    {
        $this->orderDataMock->expects($this->any())
            ->method('getPayments')
            ->will($this->returnValue($this->orderData['payments']));

        $payment = $this->getMock('Magento\Sales\Model\Order\Payment',
            [],
            [],
            '',
            false
        );

        $this->paymentConverterMock->expects($this->at(0))
            ->method('getModel')
            ->with($this->orderData['payments'][0])
            ->will($this->returnValue($payment));

        return [$payment];
    }

    public function testGetModel()
    {
        $billingAddressMock = $this->getMock('Magento\Sales\Model\Order\Address', ['__wakeup'], [], '', false);
        $shippingAddressMock = clone $billingAddressMock;
        $this->addressConverterMock->expects($this->at(0))
            ->method('getModel')
            ->with($this->orderData['billingAddress'])
            ->will($this->returnValue($billingAddressMock));
        $this->addressConverterMock->expects($this->at(1))
            ->method('getModel')
            ->with($this->orderData['shippingAddress'])
            ->will($this->returnValue($shippingAddressMock));
        $this->orderBuilderMock->expects($this->once())
            ->method('setCustomer')
            ->with($this->getCustomer())
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setQuoteId')
            ->with($this->orderData['quoteId'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setAppliedRuleIds')
            ->with($this->orderData['appliedRuleIds'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setIsVirtual')
            ->with($this->orderData['isVirtual'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setRemoteIp')
            ->with($this->orderData['remoteIp'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setBaseSubtotal')
            ->with($this->orderData['baseSubtotal'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setSubtotal')
            ->with($this->orderData['subtotal'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setBaseGrandTotal')
            ->with($this->orderData['baseGrandTotal'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setGrandTotal')
            ->with($this->orderData['grandTotal'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setBaseCurrencyCode')
            ->with($this->orderData['baseCurrencyCode'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setGlobalCurrencyCode')
            ->with($this->orderData['globalCurrencyCode'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setStoreCurrencyCode')
            ->with($this->orderData['storeCurrencyCode'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setStoreId')
            ->with($this->orderData['storeId'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setStoreToBaseRate')
            ->with($this->orderData['storeToBaseRate'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setBaseToGlobalRate')
            ->with($this->orderData['baseToGlobalRate'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setCouponCode')
            ->with($this->orderData['couponCode'])
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setBillingAddress')
            ->with($billingAddressMock)
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($shippingAddressMock)
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setPayments')
            ->with($this->getPayments())
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setItems')
            ->with($this->getItems())
            ->will($this->returnSelf());
        $orderMock = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $this->orderBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($orderMock));

        $this->assertEquals($orderMock, $this->orderConverter->getModel($this->orderDataMock));
    }
}
