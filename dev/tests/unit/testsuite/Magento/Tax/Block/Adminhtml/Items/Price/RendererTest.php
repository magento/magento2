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
namespace Magento\Tax\Block\Adminhtml\Items\Price;

use Magento\Framework\Object;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Block\Adminhtml\Items\Price\Renderer
     */
    protected $renderer;

    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemPriceRenderer;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultColumnRenderer;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->itemPriceRenderer = $this->getMockBuilder('\Magento\Tax\Block\Item\Price\Renderer')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'displayPriceInclTax',
                    'displayPriceExclTax',
                    'displayBothPrices',
                    'getTotalAmount',
                    'formatPrice',
                ]
            )
            ->getMock();

        $this->defaultColumnRenderer = $this->getMockBuilder(
            '\Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn'
        )->disableOriginalConstructor()
            ->setMethods(['displayPrices'])
            ->getMock();

        $this->renderer = $objectManager->getObject(
            '\Magento\Tax\Block\Adminhtml\Items\Price\Renderer',
            [
                'itemPriceRenderer' => $this->itemPriceRenderer,
                'defaultColumnRenderer' => $this->defaultColumnRenderer,
            ]
        );
    }

    public function testDisplayPriceInclTax()
    {
        $flag = false;
        $this->itemPriceRenderer->expects($this->once())
            ->method('displayPriceInclTax')
            ->will($this->returnValue($flag));

        $this->assertEquals($flag, $this->renderer->displayPriceInclTax());
    }

    public function testDisplayPriceExclTax()
    {
        $flag = true;
        $this->itemPriceRenderer->expects($this->once())
            ->method('displayPriceExclTax')
            ->will($this->returnValue($flag));

        $this->assertEquals($flag, $this->renderer->displayPriceExclTax());
    }

    public function testDisplayBothPrices()
    {
        $flag = true;
        $this->itemPriceRenderer->expects($this->once())
            ->method('displayBothPrices')
            ->will($this->returnValue($flag));

        $this->assertEquals($flag, $this->renderer->displayBothPrices());
    }

    public function testDisplayPrices()
    {
        $basePrice = 3;
        $price = 4;
        $display = "$3 [L4]";

        $this->defaultColumnRenderer->expects($this->once())
            ->method('displayPrices')
            ->with($basePrice, $price)
            ->will($this->returnValue($display));

        $this->assertEquals($display, $this->renderer->displayPrices($basePrice, $price));
    }

    public function testFormatPrice()
    {
        $price = 4;
        $display = "$3";

        $this->itemPriceRenderer->expects($this->once())
            ->method('formatPrice')
            ->with($price)
            ->will($this->returnValue($display));

        $this->assertEquals($display, $this->renderer->formatPrice($price));
    }

    public function testGetTotalAmount()
    {
        $totalAmount = 10;
        $itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemPriceRenderer->expects($this->once())
            ->method('getTotalAmount')
            ->with($itemMock)
            ->will($this->returnValue($totalAmount));

        $this->assertEquals($totalAmount, $this->renderer->getTotalAmount($itemMock));
    }

}
