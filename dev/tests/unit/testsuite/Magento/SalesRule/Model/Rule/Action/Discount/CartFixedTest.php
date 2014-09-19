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
namespace Magento\SalesRule\Model\Rule\Action\Discount;

class CartFixedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rule;

    /**
     * @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $item;

    /**
     * @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $data;

    /**
     * @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Sales\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $address;

    /**
     * @var CartFixed
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    protected function setUp()
    {
        $this->rule = $this->getMock('Magento\Framework\Object', null, array(), 'Rule', true);
        $this->item = $this->getMock('Magento\Sales\Model\Quote\Item\AbstractItem', array(), array(), '', false);
        $this->data = $this->getMock('Magento\SalesRule\Model\Rule\Action\Discount\Data', null);

        $this->quote = $this->getMock('Magento\Sales\Model\Quote', array(), array(), '', false);
        $this->address = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            array('getCartFixedRules', 'setCartFixedRules', '__wakeup'),
            array(),
            '',
            false
        );
        $this->item->expects($this->any())->method('getQuote')->will($this->returnValue($this->quote));
        $this->item->expects($this->any())->method('getAddress')->will($this->returnValue($this->address));

        $this->validator = $this->getMock('Magento\SalesRule\Model\Validator', array(), array(), '', false);
        $dataFactory = $this->getMock(
            'Magento\SalesRule\Model\Rule\Action\Discount\DataFactory',
            array('create'),
            array(),
            '',
            false
        );
        $dataFactory->expects($this->any())->method('create')->will($this->returnValue($this->data));
        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();
        $this->model = new CartFixed($this->validator, $dataFactory, $this->priceCurrency);
    }

    /**
     * @covers \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed::calculate
     */
    public function testCalculate()
    {
        $this->rule->setData(array('id' => 1, 'discount_amount' => 10.0));

        $this->address->expects($this->any())->method('getCartFixedRules')->will($this->returnValue(array()));
        $store = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $this->priceCurrency->expects($this->atLeastOnce())->method('convert')->will($this->returnArgument(0));
        $this->priceCurrency->expects($this->atLeastOnce())->method('round')->will($this->returnArgument(0));
        $this->quote->expects($this->any())->method('getStore')->will($this->returnValue($store));

        /** validators data */
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemPrice'
        )->with(
            $this->item
        )->will(
            $this->returnValue(100)
        );
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemBasePrice'
        )->with(
            $this->item
        )->will(
            $this->returnValue(100)
        );
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemOriginalPrice'
        )->with(
            $this->item
        )->will(
            $this->returnValue(100)
        );
        $this->validator->expects(
            $this->once()
        )->method(
            'getItemBaseOriginalPrice'
        )->with(
            $this->item
        )->will(
            $this->returnValue(100)
        );

        $this->address->expects($this->once())->method('setCartFixedRules')->with(array(1 => 0.0));
        $this->model->calculate($this->rule, $this->item, 1);

        $this->assertEquals($this->data->getAmount(), 10);
        $this->assertEquals($this->data->getBaseAmount(), 10);
        $this->assertEquals($this->data->getOriginalAmount(), 10);
        $this->assertEquals($this->data->getBaseOriginalAmount(), 100);
    }
}
