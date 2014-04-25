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
namespace Magento\Catalog\Block\Product;

/**
 * Class for testing methods of AbstractProduct
 */
class AbstractProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\View\Type\Simple
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Block\Product\Context
     */
    protected $productContextMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutMock;

    /**
     * Set up mocks and tested class
     * Child class is used as the tested class is declared abstract
     */
    public function setUp()
    {
        $this->productContextMock = $this->getMock(
            'Magento\Catalog\Block\Product\Context',
            ['getLayout'],
            [],
            '',
            false
        );
        $arrayUtilsMock = $this->getMock('Magento\Framework\Stdlib\ArrayUtils', [], [], '', false);
        $this->layoutMock = $this->getMock('Magento\Framework\View\Layout', ['getBlock'], [], '', false);

        $this->productContextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layoutMock));

        $this->block = new \Magento\Catalog\Block\Product\View\Type\Simple(
            $this->productContextMock,
            $arrayUtilsMock
        );
    }

    /**
     * Test for method getProductPrice
     *
     * @covers \Magento\Catalog\Block\Product\AbstractProduct::getProductPriceHtml
     * @covers \Magento\Catalog\Block\Product\AbstractProduct::getProductPrice
     */
    public function testGetProductPrice()
    {
        $expectedPriceHtml = '<html>Expected Price html with price $30</html>';
        $priceRenderBlock = $this->getMock('Magento\Framework\Pricing\Render', ['render'], [], '', false);
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->will($this->returnValue($priceRenderBlock));
        $priceRenderBlock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($expectedPriceHtml));

        $this->assertEquals($expectedPriceHtml, $this->block->getProductPrice($product));

    }

    /**
     * Test testGetProductPriceHtml
     */
    public function testGetProductPriceHtml()
    {
        $expectedPriceHtml = '<html>Expected Price html with price $30</html>';
        $priceRenderBlock = $this->getMock('Magento\Framework\Pricing\Render', ['render'], [], '', false);
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->will($this->returnValue($priceRenderBlock));

        $priceRenderBlock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($expectedPriceHtml));

        $this->assertEquals($expectedPriceHtml, $this->block->getProductPriceHtml(
            $product, 'price_code', 'zone_code'
        ));

    }
}