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

namespace Magento\Multishipping\Block\Checkout;

use Magento\Sales\Model\Quote\Address;

class OverviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Overview
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;
    
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->addressMock = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            [
                'getShippingMethod',
                'getShippingRateByCode',
                'getAllVisibleItems',
                'getTotals',
                'getAddressType',
                '__wakeup'
            ],
            [],
            '',
            false);

        $this->priceCurrencyMock =
            $this->getMock('Magento\Framework\Pricing\PriceCurrencyInterface', [], [], '', false);
        $this->model = $objectManager->getObject('Magento\Multishipping\Block\Checkout\Overview',
            [
                'priceCurrency' => $this->priceCurrencyMock,
            ]
        );
    }

    public function testGetShippingRateByCode()
    {
        $rateMock = $this->getMock('Magento\Sales\Model\Quote\Address\Rate', [], [], '', false);
        $this->addressMock->expects($this->once())
            ->method('getShippingMethod')->will($this->returnValue('shipping method'));
        $this->addressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with('shipping method')
            ->willReturn($rateMock);

        $this->assertEquals($rateMock, $this->model->getShippingAddressRate($this->addressMock));
    }

    public function testGetShippingRateByCodeWithEmptyRate()
    {
        $this->addressMock->expects($this->once())
            ->method('getShippingMethod')->will($this->returnValue('shipping method'));
        $this->addressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with('shipping method')
            ->willReturn(false);

        $this->assertFalse($this->model->getShippingAddressRate($this->addressMock));
    }

    public function testGetShippingAddressItems()
    {
        $this->addressMock->expects($this->once())->method('getAllVisibleItems')->willReturn(['expected array']);
        $this->assertEquals(['expected array'], $this->model->getShippingAddressItems($this->addressMock));
    }

    public function testGetShippingAddressTotals()
    {
        $totalMock =$this->getMock('\Magento\Sales\Model\Order\Total',
            [
                'getCode',
                'setTitle',
                '__wakeup'
            ],
            [],
            '',
            false);
        $this->addressMock->expects($this->once())->method('getTotals')->willReturn([$totalMock]);
        $totalMock->expects($this->once())->method('getCode')->willReturn('grand_total');
        $this->addressMock->expects($this->once())->method('getAddressType')->willReturn(Address::TYPE_BILLING);
        $totalMock->expects($this->once())->method('setTitle')->with('Total');

        $this->assertEquals([$totalMock], $this->model->getShippingAddressTotals($this->addressMock));
    }

    public function testGetShippingAddressTotalsWithNotBillingAddress()
    {
        $totalMock =$this->getMock('\Magento\Sales\Model\Order\Total',
            [
                'getCode',
                'setTitle',
                '__wakeup'
            ],
            [],
            '',
            false);
        $this->addressMock->expects($this->once())->method('getTotals')->willReturn([$totalMock]);
        $totalMock->expects($this->once())->method('getCode')->willReturn('grand_total');
        $this->addressMock->expects($this->once())->method('getAddressType')->willReturn('not billing');
        $totalMock->expects($this->once())->method('setTitle')->with('Total for this address');

        $this->assertEquals([$totalMock], $this->model->getShippingAddressTotals($this->addressMock));
    }
}
