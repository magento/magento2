<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\Widget;

class NewWidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\Widget\NewWidget|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $contextMock = $this->getMock('Magento\Catalog\Block\Product\Context', [], [], '', false, false);
        $this->layout = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));
        $contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->block = $objectManager->getObject(
            'Magento\Catalog\Block\Product\Widget\NewWidget',
            [
                'context' => $contextMock
            ]
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetProductPriceHtml()
    {
        $id = 6;
        $expectedHtml = '
        <div class="price-box price-final_price">
            <span class="regular-price" id="product-price-' . $id . '">
                <span class="price">$0.00</span>
            </span>
        </div>';
        $type = 'widget-new-list';
        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false, false);
        $productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));
        $arguments = [
            'price_id' => 'old-price-' . $id . '-' . $type,
            'display_minimal_price' => true,
            'include_container' => true,
            'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        ];

        $priceBoxMock = $this->getMock('Magento\Framework\Pricing\Render', ['render'], [], '', false, false);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($this->equalTo('product.price.render.default'))
            ->will($this->returnValue($priceBoxMock));

        $priceBoxMock->expects($this->once())
            ->method('render')
            ->with($this->equalTo('final_price'), $this->equalTo($productMock), $this->equalTo($arguments))
            ->will($this->returnValue($expectedHtml));

        $result = $this->block->getProductPriceHtml($productMock, $type);
        $this->assertEquals($expectedHtml, $result);
    }

    /**
     * @param int $pageNumber
     * @param int $expectedResult
     * @dataProvider getCurrentPageDataProvider
     */
    public function testGetCurrentPage($pageNumber, $expectedResult)
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with(\Magento\Catalog\Block\Product\Widget\NewWidget::PAGE_VAR_NAME)
            ->willReturn($pageNumber);

        $this->assertEquals($expectedResult, $this->block->getCurrentPage());
    }

    public function getCurrentPageDataProvider()
    {
        return [
            [1, 1],
            [5, 5],
            [10, 10]
        ];
    }
}
