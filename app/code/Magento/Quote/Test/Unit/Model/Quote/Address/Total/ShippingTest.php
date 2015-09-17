<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Address\Total;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\Total\Shipping
     */
    protected $shippingModel;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->shippingModel = $objectManager->getObject('Magento\Quote\Model\Quote\Address\Total\Shipping');
    }

    public function testFetch()
    {
        $shippingAmount = 100;
        $shippingDescription = 100;
        $expectedResult = [
            'code' => 'shipping',
            'value' => 100,
            'title' => __('Shipping & Handling (%1)', $shippingDescription)
        ];

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $totalMock = $this->getMock(
            '\Magento\Quote\Model\Quote\Address\Total',
            ['getShippingAmount', 'getShippingDescription'],
            [],
            '',
            false
        );

        $totalMock->expects($this->once())->method('getShippingAmount')->willReturn($shippingAmount);
        $totalMock->expects($this->once())->method('getShippingDescription')->willReturn($shippingDescription);
        $this->assertEquals($expectedResult, $this->shippingModel->fetch($quoteMock, $totalMock));
    }
}
