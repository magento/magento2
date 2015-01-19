<?php
/**
 * \Magento\Wishlist\Block\Item\Configure
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Block\Item;

class ConfigureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Block\Item\Configure
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mockRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mockContext;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mockWishlistData;

    public function setUp()
    {
        $this->_mockWishlistData = $this->getMockBuilder(
            'Magento\Wishlist\Helper\Data'
        )->disableOriginalConstructor()->getMock();
        $this->_mockContext = $this->getMockBuilder(
            'Magento\Framework\View\Element\Template\Context'
        )->disableOriginalConstructor()->getMock();
        $this->_mockRegistry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_model = new \Magento\Wishlist\Block\Item\Configure(
            $this->_mockContext,
            $this->_mockWishlistData,
            $this->_mockRegistry
        );
    }

    public function testGetWishlistOptions()
    {
        $typeId = 'simple';
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $product->expects($this->once())->method('getTypeId')->willReturn($typeId);
        $this->_mockRegistry->expects($this->once())
            ->method('registry')
            ->with($this->equalTo('product'))
            ->willReturn($product);

        $this->assertEquals(['productType' => $typeId], $this->_model->getWishlistOptions());
    }

    public function testGetProduct()
    {
        $product = 'some test product';
        $this->_mockRegistry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            $this->equalTo('product')
        )->willReturn(
            $product
        );

        $this->assertEquals($product, $this->_model->getProduct());
    }

    public function testSetLayout()
    {
        $layoutMock = $this->getMock(
            'Magento\Framework\View\LayoutInterface',
            [],
            [],
            '',
            false
        );

        $blockMock = $this->getMock(
            'Magento\Framework\View\Element\AbstractBlock',
            ['setCustomAddToCartUrl'],
            [],
            '',
            false
        );
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->will($this->returnValue($blockMock));

        $itemMock = $this->getMock(
            'Magento\Wishlist\Model\Item',
            [],
            [],
            '',
            false
        );

        $this->_mockRegistry->expects($this->exactly(2))
            ->method('registry')
            ->with('wishlist_item')
            ->willReturn($itemMock);

        $this->_mockWishlistData->expects($this->once())
            ->method('getAddToCartUrl')
            ->with($itemMock)
            ->willReturn('some_url');

        $blockMock->expects($this->once())
            ->method('setCustomAddToCartUrl')
            ->with('some_url');

        $this->assertEquals($this->_model, $this->_model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->_model->getLayout());
    }

    public function testSetLayoutWithNoItem()
    {
        $layoutMock = $this->getMock(
            'Magento\Framework\View\LayoutInterface',
            [],
            [],
            '',
            false
        );

        $blockMock = $this->getMock(
            'Magento\Framework\View\Element\AbstractBlock',
            ['setCustomAddToCartUrl'],
            [],
            '',
            false
        );
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->willReturn($blockMock);

        $this->_mockRegistry->expects($this->exactly(1))
            ->method('registry')
            ->with('wishlist_item')
            ->willReturn(null);

        $this->_mockWishlistData->expects($this->never())
            ->method('getAddToCartUrl');

        $blockMock->expects($this->never())
            ->method('setCustomAddToCartUrl');

        $this->assertEquals($this->_model, $this->_model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->_model->getLayout());
    }

    public function testSetLayoutWithNoBlockAndItem()
    {
        $layoutMock = $this->getMock(
            'Magento\Framework\View\LayoutInterface',
            [],
            [],
            '',
            false
        );

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->willReturn(null);

        $this->_mockRegistry->expects($this->never())
            ->method('registry');

        $this->_mockWishlistData->expects($this->never())
            ->method('getAddToCartUrl');

        $this->assertEquals($this->_model, $this->_model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->_model->getLayout());
    }
}
