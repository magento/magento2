<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model\Config;

use Magento\Braintree\Model\Config;
use Magento\Braintree\Model\Config\PayPal;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ConfigTest
 *
 */
class PayPalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\Config\PayPal
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreeConfigurationMock;

    /**
     * @var \Magento\Framework\DB\TransactionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreeClientTokenMock;

    /**
     * @var \Magento\Braintree\Model\System\Config\Source\Country|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceCountryMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            '\Magento\Braintree\Model\Config\PayPal',
            [
                'scopeConfig' => $this->scopeConfigMock,
            ]
        );
    }

    public function testIsActive()
    {
        $prefix = 'payment/braintree_paypal/';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($prefix . Config::KEY_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn(true);
        $this->assertEquals(true, $this->model->isActive());
    }

    public function testGetMerchantNameOverride()
    {
        $prefix = 'payment/braintree_paypal/';
        $merchantName = 'merchantName';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($prefix . PayPal::KEY_MERCHANT_NAME_OVERRIDE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn($merchantName);
        $this->assertEquals($merchantName, $this->model->getMerchantNameOverride());
    }

    public function testIsShortcutCheckoutEnabled()
    {
        $isEnabled = 1;
        $prefix = 'payment/braintree_paypal/';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $prefix . PayPal::KEY_DISPLAY_ON_SHOPPING_CART,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )->willReturn($isEnabled);
        $this->assertEquals(true, $this->model->isShortcutCheckoutEnabled());
    }

    public function testIsBillingAddressEnabled()
    {
        $isEnabled = 1;
        $prefix = 'payment/braintree_paypal/';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $prefix . PayPal::KEY_REQUIRE_BILLING_ADDRESS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )->willReturn($isEnabled);
        $this->assertEquals(true, $this->model->isBillingAddressEnabled());
    }
}
