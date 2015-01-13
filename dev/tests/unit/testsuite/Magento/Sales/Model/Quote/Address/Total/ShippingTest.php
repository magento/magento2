<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Address\Total;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Quote\Address\Total\Shipping
     */
    protected $shippingModel;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->shippingModel = $objectManager->getObject('Magento\Sales\Model\Quote\Address\Total\Shipping');
    }

    /**
     * @dataProvider fetchDataProvider
     */
    public function testFetch($shippingAmount, $shippingDescription, $expectedTotal)
    {
        $address = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            ['getShippingAmount', 'getShippingDescription', 'addTotal', '__wakeup'],
            [],
            '',
            false
        );

        $address->expects($this->once())->method('getShippingAmount')->will($this->returnValue($shippingAmount));

        $address->expects(
            $this->once()
        )->method(
            'getShippingDescription'
        )->will(
            $this->returnValue($shippingDescription)
        );

        $address->expects(
            $this->once()
        )->method(
            'addTotal'
        )->with(
            $this->equalTo($expectedTotal)
        )->will(
            $this->returnSelf()
        );

        $this->assertEquals($this->shippingModel, $this->shippingModel->fetch($address));
    }

    public function fetchDataProvider()
    {
        return [
            [
                'shipping_amount' => 1,
                'shipping_description' => 'Shipping Method',
                'expected' => [
                    'code' => 'shipping',
                    'title' => __('Shipping & Handling (%1)', 'Shipping Method'),
                    'value' => 1,
                ],
            ],
            [
                'shipping_amount' => 1,
                'shipping_description' => '',
                'expected' => ['code' => 'shipping', 'title' => __('Shipping & Handling'), 'value' => 1]
            ]
        ];
    }
}
