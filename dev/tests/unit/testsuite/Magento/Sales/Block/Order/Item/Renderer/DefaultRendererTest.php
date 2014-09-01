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
namespace Magento\Sales\Block\Order\Item\Renderer;

class DefaultRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Block\Template
     */
    protected $priceRenderBlock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Layout
     */
    protected $layoutMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote\Item  */
    protected $itemMock;

    /**
     * Initialize required data
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->layoutMock = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();

        $this->block = $this->objectManager->getObject(
            'Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer',
            array(
                'context' => $this->objectManager->getObject(
                        'Magento\Backend\Block\Template\Context',
                        array('layout' => $this->layoutMock)
                    )
            )
        );

        $this->priceRenderBlock = $this->getMockBuilder('\Magento\Backend\Block\Template')
            ->disableOriginalConstructor()
            ->setMethods(['setItem', 'toHtml'])
            ->getMock();

        $itemMockMethods = [
            '__wakeup',
            'getRowTotal',
            'getTaxAmount',
            'getDiscountAmount',
            'getHiddenTaxAmount',
            'getWeeeTaxAppliedRowAmount'
        ];
        $this->itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods($itemMockMethods)
            ->getMock();
    }

    public function testGetItemPriceHtml()
    {
        $html = '$34.28';

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_unit_price')
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));


        $this->assertEquals($html, $this->block->getItemPriceHtml($this->itemMock));
    }

    public function testGetItemRowTotalHtml()
    {
        $html = '$34.28';

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_row_total')
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));


        $this->assertEquals($html, $this->block->getItemRowTotalHtml($this->itemMock));
    }

    public function testGetItemRowTotalAfterDiscountHtml()
    {
        $html = '$34.28';

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_row_total_after_discount')
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));


        $this->assertEquals($html, $this->block->getItemRowTotalAfterDiscountHtml($this->itemMock));
    }

    public function testGetTotalAmount()
    {
        $rowTotal = 100;
        $taxAmount = 10;
        $hiddenTaxAmount = 2;
        $discountAmount = 20;
        $weeeTaxAppliedRowAmount = 10;

        $expectedResult = $rowTotal + $taxAmount + $hiddenTaxAmount - $discountAmount + $weeeTaxAppliedRowAmount;
        $this->itemMock->expects($this->once())
            ->method('getRowTotal')
            ->will($this->returnValue($rowTotal));
        $this->itemMock->expects($this->once())
            ->method('getTaxAmount')
            ->will($this->returnValue($taxAmount));
        $this->itemMock->expects($this->once())
            ->method('getHiddenTaxAmount')
            ->will($this->returnValue($hiddenTaxAmount));
        $this->itemMock->expects($this->once())
            ->method('getDiscountAmount')
            ->will($this->returnValue($discountAmount));
        $this->itemMock->expects($this->once())
            ->method('getWeeeTaxAppliedRowAmount')
            ->will($this->returnValue($weeeTaxAppliedRowAmount));

        $this->assertEquals($expectedResult, $this->block->getTotalAmount($this->itemMock));
    }
}
