<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Block\Onepage;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ProgressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Selected shipping method
     */
    const SHIPPING_METHOD = 'shipping method';

    /**
     * Price of selected shipping method
     */
    const SHIPPING_PRICE = 13.02;

    /**
     * Price of selected shipping method wrapped with tax helper
     */
    const SHIPPING_PRICE_WITH_TAX = 13.03;

    /**
     * Price of selected shipping method formatted with current store
     */
    const SHIPPING_PRICE_FORMATTED = '$13.38';

    /**
     * @var \Magento\Checkout\Block\Onepage\Progress
     */
    protected $model;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelper;

    /**
     * @var \Magento\Sales\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddress;

    protected function setUp()
    {
        $this->checkoutSession = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->taxHelper = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Checkout\Block\Onepage\Progress',
            ['resourceSession' => $this->checkoutSession, 'taxData' => $this->taxHelper]
        );
        $this->shippingAddress = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            ['getShippingRateByCode', '__wakeup'],
            [],
            '',
            false
        );
        $this->store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $quote = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $quote->expects($this->any())->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $quote->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $this->checkoutSession->expects($this->any())->method('getQuote')->will($this->returnValue($quote));
    }

    /**
     * Test getShippingRate method
     */
    public function testGetShippingRate()
    {
        $rate = $this->getMock('Magento\Sales\Model\Quote\Address\Rate', ['__wakeup'], [], '', false);
        $this->shippingAddress->setShippingMethod(self::SHIPPING_METHOD);
        $this->shippingAddress->expects($this->once())
            ->method('getShippingRateByCode')
            ->with(self::SHIPPING_METHOD)
            ->will($this->returnValue($rate));

        $this->assertEquals($rate, $this->model->getShippingRate());
    }
}
