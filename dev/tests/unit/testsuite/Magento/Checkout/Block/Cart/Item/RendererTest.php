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
namespace Magento\Checkout\Block\Cart\Item;

use Magento\Checkout\Block\Cart\Item\Renderer;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imageHelper;

    /**
     * @var Renderer
     */
    protected $_renderer;

    /**
     * @var \Magento\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    protected function setUp()
    {
        parent::setUp();
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_imageHelper = $this->getMock('Magento\Catalog\Helper\Image', array(), array(), '', false);
        $this->layout = $this->getMock('Magento\Framework\View\LayoutInterface');

        $context = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));

        $this->_renderer = $objectManagerHelper->getObject(
            'Magento\Checkout\Block\Cart\Item\Renderer',
            array('imageHelper' => $this->_imageHelper, 'context' => $context)
        );
    }

    public function testGetProductForThumbnail()
    {
        $product = $this->_initProduct();
        $productForThumbnail = $this->_renderer->getProductForThumbnail();
        $this->assertEquals($product->getName(), $productForThumbnail->getName(), 'Invalid product was returned.');
    }

    public function testGetProductThumbnail()
    {
        $productForThumbnail = $this->_initProduct();
        /** Ensure that image helper was initialized with correct arguments */
        $this->_imageHelper->expects(
            $this->once()
        )->method(
            'init'
        )->with(
            $productForThumbnail,
            'thumbnail'
        )->will(
            $this->returnSelf()
        );
        $productThumbnail = $this->_renderer->getProductThumbnail();
        $this->assertSame($this->_imageHelper, $productThumbnail, 'Invalid product thumbnail is returned.');
    }

    /**
     * Initialize product.
     *
     * @return \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _initProduct()
    {
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getName', '__wakeup', 'getIdentities'),
            array(),
            '',
            false
        );
        $product->expects($this->any())->method('getName')->will($this->returnValue('Parent Product'));

        /** @var \Magento\Sales\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', array(), array(), '', false);
        $item->expects($this->any())->method('getProduct')->will($this->returnValue($product));

        $this->_renderer->setItem($item);
        return $product;
    }

    public function testGetIdentities()
    {
        $product = $this->_initProduct();
        $identities = [1 => 1, 2 => 2, 3 => 3];
        $product->expects($this->exactly(2))
            ->method('getIdentities')
            ->will($this->returnValue($identities));

        $this->assertEquals($product->getIdentities(), $this->_renderer->getIdentities());
    }

    public function testGetIdentitiesFromEmptyItem()
    {
        $this->assertEmpty($this->_renderer->getIdentities());
    }

    /**
     * @covers \Magento\Checkout\Block\Cart\Item\Renderer::getProductPriceHtml
     * @covers \Magento\Checkout\Block\Cart\Item\Renderer::getPriceRender
     */
    public function testGetProductPriceHtml()
    {
        $priceHtml = 'some price html';
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $priceRender = $this->getMockBuilder('Magento\Framework\Pricing\Render')
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout->expects($this->atLeastOnce())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->will($this->returnValue($priceRender));

        $priceRender->expects($this->once())
            ->method('render')
            ->with(
                \Magento\Catalog\Pricing\Price\ConfiguredPriceInterface::CONFIGURED_PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            )->will($this->returnValue($priceHtml));

        $this->assertEquals($priceHtml, $this->_renderer->getProductPriceHtml($product));
    }
}
