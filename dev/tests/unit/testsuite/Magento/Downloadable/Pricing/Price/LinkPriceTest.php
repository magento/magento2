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

namespace Magento\Downloadable\Pricing\Price;

/**
 * Class LinkPriceTest
 */
class LinkPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Pricing\Price\LinkPrice
     */
    protected $linkPrice;

    /**
     * @var \Magento\Framework\Pricing\Amount\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    /**
     * @var \Magento\Downloadable\Model\Resource\Link|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkMock;

    /**
     * Test setUp
     */
    protected function setUp()
    {
        $this->saleableItemMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->amountMock = $this->getMock('Magento\Framework\Pricing\Amount\Base', [], [], '', false);
        $this->calculatorMock = $this->getMock('Magento\Framework\Pricing\Adjustment\Calculator', [], [], '', false);
        $this->linkMock = $this->getMock(
            'Magento\Downloadable\Model\Link',
            ['getPrice', 'getProduct', '__wakeup'],
            [],
            '',
            false
        );

        $this->linkPrice = new LinkPrice($this->saleableItemMock, 1, $this->calculatorMock);
    }

    public function testGetLinkAmount()
    {
        $amount = 100;

        $this->linkMock->expects($this->once())
            ->method('getPrice')
            ->will($this->returnValue($amount));
        $this->linkMock->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($this->saleableItemMock));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($amount, $this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($amount));

        $result = $this->linkPrice->getLinkAmount($this->linkMock);
        $this->assertEquals($amount, $result);
    }

} 