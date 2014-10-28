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
namespace Magento\Tax\Block\Checkout\Cart\Sidebar;

use Magento\Framework\Object;

class TotalsTest extends \PHPUnit_Framework_TestCase
{
    const SUBTOTAL_EXCL_TAX = 9.8;
    const SUBTOTAL_INCL_TAX = 10.3;
    const SUBTOTAL = 10;

    /**
     * @var \Magento\Tax\Block\Checkout\Cart\Sidebar\Totals
     */
    protected $totalsObj;

    /**
     * @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Tax\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxConfig;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelper;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->quote = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getTotals', '__wakeup'])
            ->getMock();

        $checkoutSession = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', '__wakeup'])
            ->getMock();

        $checkoutSession->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quote));

        $this->taxHelper = $this->getMockBuilder('\Magento\Tax\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([
                'getIncExcText'
            ])
            ->getMock();

        $this->taxConfig = $this->getMockBuilder('\Magento\Tax\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods([
                'displayCartSubtotalInclTax', 'displayCartSubtotalExclTax', 'displayCartSubtotalBoth'
            ])
            ->getMock();

        $this->totalsObj = $objectManager->getObject(
            '\Magento\Tax\Block\Checkout\Cart\Sidebar\Totals',
            [
                'checkoutSession' => $checkoutSession,
                'taxHelper' => $this->taxHelper,
                'taxConfig' => $this->taxConfig,
            ]
        );
    }

    /**
     * @dataProvider getSubtotalInclTaxDataProvider
     */
    public function testGetSubtotalInclTax($totals, $expectedValue)
    {
        $this->quote->expects($this->once())
            ->method('getTotals')
            ->will($this->returnValue($totals));

        $this->assertEquals($expectedValue, $this->totalsObj->getSubtotalInclTax());
    }

    public function getSubtotalInclTaxDataProvider()
    {
        $data = [
            'incl' => [
                'totals' => [
                    'subtotal' => new Object(
                            [
                                'value_incl_tax' => self::SUBTOTAL_INCL_TAX,
                                'value' => self::SUBTOTAL,
                            ]
                        ),
                ],
                'expected' => self::SUBTOTAL_INCL_TAX,
            ],
            'no_incl_value' => [
                'totals' => [
                    'subtotal' => new Object(
                            [
                                'value' => self::SUBTOTAL,
                            ]
                        ),
                ],
                'expected' => self::SUBTOTAL,
            ],
            'no_subtotal' => [
                'totals' => [],
                'expected' => 0,
            ]
        ];
        return $data;
    }

    /**
     * @dataProvider getSubtotalExclTaxDataProvider
     */
    public function testGetSubtotalExclTax($totals, $expectedValue)
    {
        $this->quote->expects($this->once())
            ->method('getTotals')
            ->will($this->returnValue($totals));

        $this->assertEquals($expectedValue, $this->totalsObj->getSubtotalExclTax());
    }

    public function getSubtotalExclTaxDataProvider()
    {
        $data = [
            'excl' => [
                'totals' => [
                    'subtotal' => new Object(
                            [
                                'value_excl_tax' => self::SUBTOTAL_EXCL_TAX,
                                'value' => self::SUBTOTAL,
                            ]
                        ),
                ],
                'expected' => self::SUBTOTAL_EXCL_TAX,
            ],
            'no_excl_value' => [
                'totals' => [
                    'subtotal' => new Object(
                            [
                                'value' => self::SUBTOTAL,
                            ]
                        ),
                ],
                'expected' => self::SUBTOTAL,
            ],
            'no_subtotal' => [
                'totals' => [],
                'expected' => 0,
            ]
        ];
        return $data;
    }

    public function testGetDisplaySubtotalInclTax()
    {
        $this->taxConfig->expects($this->once())
            ->method('displayCartSubtotalInclTax');

        $this->totalsObj->getDisplaySubtotalInclTax();
    }

    public function testGetDisplaySubtotalExclTax()
    {
        $this->taxConfig->expects($this->once())
            ->method('displayCartSubtotalExclTax');

        $this->totalsObj->getDisplaySubtotalExclTax();
    }

    public function testGetDisplaySubtotalBoth()
    {
        $this->taxConfig->expects($this->once())
            ->method('displayCartSubtotalBoth');

        $this->totalsObj->getDisplaySubtotalBoth();
    }

    public function testGetIncExcTaxLabel()
    {
        $flag = true;
        $text = "Incl. Tax";
        $expected = " (Incl. Tax)";
        $this->taxHelper->expects($this->once())
            ->method('getIncExcText')
            ->with($flag)
            ->will($this->returnValue($text));

        $this->assertEquals($expected, $this->totalsObj->getIncExcTaxLabel($flag));
    }
}
